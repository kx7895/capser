<?php

namespace App\Service;

use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\InvoiceMailing;
use App\Entity\InvoiceMailingRecipient;
use App\Repository\InvoiceRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;

class InvoiceCreateService
{

    private const TAXTYPE_RC_DE = 'Reverse Charge: Steuerschuldnerschaft des Leistungsempfängers.';
    private const TAXTYPE_RC_EN = 'Reverse charge: The recipient is liable for VAT.';
    private const TAXTYPE_NOT_DE = 'Leistung unterliegt nicht den inländischen Steuern.';
    private const TAXTYPE_NOT_EN = 'Service not subject to domestic taxes.';

    public function __construct(
        private readonly InvoiceRepository      $invoiceRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Security               $security,
        private readonly LoggerInterface        $logger,
        private readonly MailerInterface        $mailer,
        private readonly string                 $mailer_email,
        private readonly string                 $mailer_name,
    ) {}

    /**
     * Diese Funktion baut den Dateinamen für finale Invoices inklusive des "files/"-Bestandteils auf und gibt ihn zurück.
     *
     * @param Invoice $invoice
     * @return string
     */
    public function buildInvoiceStorageFilename(Invoice $invoice): string
    {
        return 'files/invoices/'.$invoice->getInvoiceType()->getType().$invoice->getNumber().'-'.uniqid().'.pdf';
    }

    /**
     * Diese Funktion baut den lesbaren, schönen Dateinamen für finale Invoices auf und gibt ihn zurück.
     *
     * @param Invoice $invoice
     * @return string
     */
    public function buildInvoiceNiceFilename(Invoice $invoice): string
    {
        return $invoice->getInvoiceType()->getType().$invoice->getNumber().'.pdf';
    }

    /**
     * Diese Funktion gibt für den übergebenen Dateinamen den vollständigen Systempfad zurück.
     *
     * @param string $filename
     * @return string
     */
    public function buildFullPathToFile(string $filename): string
    {
        return getcwd().'/'.$filename;
    }

    /**
     * Abhängig von der Art der Invoice ($invoice->type), ihrer Fälligkeit ($invoice->due) und ihrer Sprache ($invoice->language), gibt diese Funktion die korrekte Zahlungsbedingung als vollständigen Satz zurück.
     *
     * @param Invoice $invoice
     * @return string
     * @todo Es gibt in termOfPayment eigentlich eine extra Angabe zum Satz auf der Rechnung, die sollte auch verwendet werden.
     */
    public function getPaymentSentence(Invoice $invoice): string
    {
        $lang = $invoice->getLanguage()->getAlpha2();
        $type = $invoice->getInvoiceType()->getType();

        if(in_array($type, ['CR', 'RV', 'GU', 'ST'])) {
            return ($lang == 'DE' ? 'Die Verrechnung dieser Gutschrift erfolgt mit der nächstmöglichen Rechnung.' : 'We settle this amount against your next invoice.');
        } elseif($invoice->getDate() == $invoice->getDue()) {
            return ($lang == 'DE' ? 'Der Gesamtbetrag ist sofort zur Zahlung fällig. Bitte überweisen Sie den Gesamtbetrag in _CURRENCY_ auf das unten angegebene Konto.' : 'The total amount is due for immediate payment. Please transfer the total amount in _CURRENCY_ to the account indicated below.');
        } else {
            return ($lang == 'DE' ? 'Bitte überweisen Sie den Gesamtbetrag bis zum '.$invoice->getDue()->format('d.m.Y').' in _CURRENCY_ auf das unten angegebene Konto.' : 'The total amount is due for payment until '.$invoice->getDue()->format('d.m.Y').'. Please transfer the total amount in _CURRENCY_ to the account indicated below.');
        }
    }

    /**
     * Abhängig von der Steuerart der Invoice ($invoice->vatType) und ihrer Sprache ($invoice->language), gibt diese Funktion die anwendbare Steuerregel als vollständigen Satz oder null (wenn nichts anwendbar ist) zurück.
     *
     * @param Invoice $invoice
     * @return string|null
     */
    public function getVatSentence(Invoice $invoice): ?string
    {
        if($invoice->getVatType() == null)
            return null;

        if($invoice->getLanguage()->getAlpha2() == 'DE') {
            if($invoice->getVatType() == 'RC')
                return self::TAXTYPE_RC_DE;
            elseif($invoice->getVatType() == 'NOT')
                return self::TAXTYPE_NOT_DE;
        } else {
            if($invoice->getVatType() == 'RC')
                return self::TAXTYPE_RC_EN;
            elseif($invoice->getVatType() == 'NOT')
                return self::TAXTYPE_NOT_EN;
        }

        return null;
    }

    /**
     * Abhängig von dem Rechnungsempfänger ($customer) - insbesondere gibt es einzelne spezifische Rechnungsempfänger mit besonderen Bedingungen sowie ihrer UID folgend - und dem Steuersatz ($taxRate), gibt diese Funktion die anwendbare Steuerart ($taxType) zurück.
     *
     * @param Customer $customer
     * @param float $taxRate
     * @return string|null
     */
    public function getVatType(Customer $customer, float $taxRate): ?string
    {
        if($customer->getPrincipal()->getAddressLine3() == 'Deutschland') {
            if($customer->getAddressLineCountry()->getAlpha3() == 'ARE' || $customer->getAddressLineCountry()->getAlpha3() == 'PAK')
                return 'NOT';

            if(!empty($customer->getVatId()) && $taxRate == 0)
                return 'RC';
        }

        return null;
    }

    public function buildInvoiceNumber(int $fibuDocumentNumberRange): ?int
    {
        try {
            return $this->invoiceRepository->getNextAvailableDocumentNumber($fibuDocumentNumberRange);
        } catch(Exception) {
            return null;
        }
    }

    public function sendToFibu(Invoice $invoice): bool
    {
        $email = new Email();

        // TO
        $receivers = [];
        if($invoice->getPrincipal()->getFibuRecipientEmail1())
            $receivers[] = $invoice->getPrincipal()->getFibuRecipientEmail1();
        if($invoice->getPrincipal()->getFibuRecipientEmail2())
            $receivers[] = $invoice->getPrincipal()->getFibuRecipientEmail2();
        if($invoice->getPrincipal()->getFibuRecipientEmail3())
            $receivers[] = $invoice->getPrincipal()->getFibuRecipientEmail3();
        if(count($receivers) == 0)
            return false;
        $email->to(...$receivers);

        $document = $invoice->getInvoiceType()->getType().' '.$invoice->getNumber();

        // TEXT
        $email->subject($invoice->getPrincipal()->getName().' | Beleg '.$document);
        $email->text('Sehr geehrte Damen und Herren,

zur Ablage und/oder weiteren buchhalterischen Verarbeitung, erhalten Sie anliegend den Beleg mit der Belegnummer '.$document.'.

Mit freundlichen Grüssen
'.$invoice->getPrincipal()->getName().'

Service: '.$this->mailer_name.' System-Mailer ('.$this->mailer_email.')');

        // ATTACHMENT
        $email->addPart(new DataPart(new File($this->buildFullPathToFile($invoice->getStorageFilename())), $invoice->getNiceFilename()));

        try {
            $this->mailer->send($email);
            $this->sendLog($invoice, $email);
            return true;
        } catch(TransportExceptionInterface $e) {
            $this->logger->warning('Mail-sendToFibu FAILED: Mail konnte nicht verschickt werden.', [$e->getCode(), $e->getMessage()]);
            return false;
        }
    }

    public function sendToCustomer(Invoice $invoice, bool $sendAsReminder = false): bool
    {
        $email = new Email();

        // TO
        $tos = [];
        $_tos = $invoice->getCustomer()->getCustomerInvoiceRecipients();
        if(count($_tos) == 0)
            return false;
        foreach($_tos as $_to)
            $tos[] = $_to->getEmail();
        $email->to(...$tos);

        $email->cc($this->security->getUser()->getUserIdentifier());

        $document = $invoice->getInvoiceType()->getType().' '.$invoice->getNumber();

        // TEXT
        if($invoice->getLanguage()->getAlpha2() == 'DE') {
            if($sendAsReminder) {
                $subject = $invoice->getPrincipal()->getName().' | Zahlungserinnerung '.$document;
                $message = 'wir möchten Sie mit dieser Nachricht daran erinnern, dass unsere Rechnung '.$document.' vom '.$invoice->getDate()->format('d.m.Y').' noch zur Zahlung fällig ist und Sie bitten, den offenen Betrag zu begleichen.';
            } else {
                $subject = $invoice->getPrincipal()->getName().' | Beleg '.$document;
                $message = 'anliegend erhalten Sie unseren Beleg mit der Belegnummer '.$document.'.';
            }

            $email->subject($subject);
            $email->text('Sehr geehrte Damen und Herren,

'.$message.'

Mit freundlichen Grüssen
'.$invoice->getPrincipal()->getName().'

Service: '.$this->mailer_name.' System-Mailer ('.$this->mailer_email.')');
        } else {
            if($sendAsReminder) {
                $subject = $invoice->getPrincipal()->getName().' | Payment Reminder '.$document;
                $message = 'We would like to remind you that our invoice '.$document.' (date: '.$invoice->getDate()->format('Y-m-d').') is still due for payment and ask you to settle the outstanding amount.';
            } else {
                $subject = $invoice->getPrincipal()->getName().' | Document '.$document;
                $message = 'Please find enclosed the document '.$document.'.';
            }

            $email->subject($subject);
            $email->text('Dear Sir or Madam, Dear All,

'.$message.'

Kind Regards,
'.$invoice->getPrincipal()->getName().'

Service: '.$this->mailer_name.' Mailing System ('.$this->mailer_email.')');
        }

        // ATTACHMENT
        $email->addPart(new DataPart(new File($this->buildFullPathToFile($invoice->getStorageFilename())), $invoice->getNiceFilename()));

        // FURTHER ATTACHMENTS (if exist)
        foreach($invoice->getInvoiceAttachments() AS $invoiceAttachment) {
            $email->addPart(new DataPart(new File($this->buildFullPathToFile($invoiceAttachment->getStorageFilename())), $invoiceAttachment->getNiceFilename()));
        }

        try {
            $this->mailer->send($email);
            $this->sendLog($invoice, $email);
            if($sendAsReminder)
                $invoice->setReminded(true);
            else
                $invoice->setSent(true);
            $this->entityManager->persist($invoice);
            $this->entityManager->flush();
            return true;
        } catch(TransportExceptionInterface $e) {
            $this->logger->warning('Mail-sendToReceiver FAILED: Mail konnte nicht verschickt werden.', [$e->getCode(), $e->getMessage()]);
            return false;
        }
    }

    private function sendLog(Invoice $invoice, Email $email): void
    {
        $invoiceMailing = new InvoiceMailing();
        $invoiceMailing->setInvoice($invoice);
        $invoiceMailing->setMailedAt(new DateTimeImmutable());
        $invoiceMailing->setMailedBy($this->security->getUser());
        $invoiceMailing->setSubject($email->getSubject());
        $invoiceMailing->setMessage($email->getBody()->toString());

        foreach($email->getTo() as $recipient) {
            $invoiceMailingRecipient = new InvoiceMailingRecipient();
            $invoiceMailingRecipient->setEmailAddress($recipient->getAddress());
            $invoiceMailingRecipient->setEmailAddressType('TO');
            $invoiceMailing->addInvoiceMailingRecipient($invoiceMailingRecipient);
        }

        foreach($email->getCc() as $recipient) {
            $invoiceMailingRecipient = new InvoiceMailingRecipient();
            $invoiceMailingRecipient->setEmailAddress($recipient->getAddress());
            $invoiceMailingRecipient->setEmailAddressType('CC');
            $invoiceMailing->addInvoiceMailingRecipient($invoiceMailingRecipient);
        }

        foreach($email->getBcc() as $recipient) {
            $invoiceMailingRecipient = new InvoiceMailingRecipient();
            $invoiceMailingRecipient->setEmailAddress($recipient->getAddress());
            $invoiceMailingRecipient->setEmailAddressType('BCC');
            $invoiceMailing->addInvoiceMailingRecipient($invoiceMailingRecipient);
        }

        $this->entityManager->persist($invoiceMailing);
//        $this->entityManager->flush();
        // TODO: Prüfe ob hier ein flush nötig ist, oder ob dies immer in den aufrufenden Funktionen erfolgen kann.
    }

}
