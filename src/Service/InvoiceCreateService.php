<?php

namespace App\Service;

use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\InvoiceMailing;
use App\Entity\InvoiceMailingRecipient;
use App\Entity\InvoicePosition;
use App\Repository\InvoiceRepository;
use App\Repository\InvoiceTypeRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
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
        private readonly InvoiceTypeRepository  $invoiceTypeRepository,
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

        if(!$invoice->isInvoice()) {
            return (($lang == 'DE' || $lang == 'CH') ? 'Die Verrechnung dieser Gutschrift erfolgt mit der nächstmöglichen Rechnung.' : 'We settle this amount against your next invoice.');
        } elseif($invoice->getDate() == $invoice->getDue()) {
            return (($lang == 'DE' || $lang == 'CH') ? 'Der Gesamtbetrag ist sofort zur Zahlung fällig. Bitte überweisen Sie den Gesamtbetrag in _CURRENCY_ auf das unten angegebene Konto.' : 'The total amount is due for immediate payment. Please transfer the total amount in _CURRENCY_ to the account indicated below.');
        } else {
            return (($lang == 'DE' || $lang == 'CH') ? 'Bitte überweisen Sie den Gesamtbetrag bis zum '.$invoice->getDue()->format('d.m.Y').' in _CURRENCY_ auf das unten angegebene Konto.' : 'The total amount is due for payment until '.$invoice->getDue()->format('Y-m-d').'. Please transfer the total amount in _CURRENCY_ to the account indicated below.');
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

        if($invoice->getLanguage()->getAlpha2() == 'DE' || $invoice->getLanguage()->getAlpha2() == 'CH') {
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

        // FROM
        $email->from(new Address($this->mailer_email, $invoice->getPrincipal()->getName()));

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
        $email->text('Sehr geehrte Damen und Herren

Zur Ablage und/oder weiteren buchhalterischen Verarbeitung, erhalten Sie anliegend den Beleg mit der Belegnummer '.$document.' vom '.$invoice->getDate()->format('d.m.Y').'..

Mit freundlichen Grüssen
'.$this->mailer_name.' Automailer
Im Auftrag von: '.$invoice->getPrincipal()->getName());

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

        // FROM
        $email->from(new Address($this->mailer_email, $invoice->getPrincipal()->getName()));

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

        $principalFooter = $invoice->getPrincipal()->getName();
        if($invoice->getPrincipal()->getAddressLine1())
            $principalFooter .= '
'.$invoice->getPrincipal()->getAddressLine1();
        if($invoice->getPrincipal()->getAddressLine2())
            $principalFooter .= '
'.$invoice->getPrincipal()->getAddressLine2();
        if($invoice->getPrincipal()->getAddressLine3())
            $principalFooter .= '
'.$invoice->getPrincipal()->getAddressLine3();
        if($invoice->getPrincipal()->getAddressLine4())
            $principalFooter .= '
'.$invoice->getPrincipal()->getAddressLine4();
        if($invoice->getPrincipal()->getAddressLineCountry())
            $principalFooter .= '
'.$invoice->getPrincipal()->getAddressLineCountry()->getName();
        $principalFooter .= '
';

        // TEXT
        if($invoice->getLanguage()->getAlpha3() == 'DEU') {
            if($sendAsReminder) {
                $subject = $invoice->getPrincipal()->getName().' | Zahlungserinnerung '.$document;
                $message = 'wir möchten Sie mit dieser Nachricht daran erinnern, dass unsere Rechnung '.$document.' vom '.$invoice->getDate()->format('d.m.Y').' noch zur Zahlung fällig ist und Sie bitten, den offenen Betrag zu begleichen.';
            } else {
                $subject = $invoice->getPrincipal()->getName().' | Beleg '.$document;
                $message = 'anliegend erhalten Sie unseren Beleg mit der Belegnummer '.$document.' vom '.$invoice->getDate()->format('d.m.Y').'.';
            }
            $email->subject($subject);
            $email->text('Sehr geehrte Damen und Herren,

'.$message.'

Mit freundlichen Grüßen

'.$principalFooter);

        } elseif($invoice->getLanguage()->getAlpha3() == 'GSW') {
            if($sendAsReminder) {
                $subject = $invoice->getPrincipal()->getName().' | Zahlungserinnerung '.$document;
                $message = 'Wir möchten Sie mit dieser Nachricht daran erinnern, dass unsere Rechnung '.$document.' vom '.$invoice->getDate()->format('d.m.Y').' noch zur Zahlung fällig ist und Sie bitten, den offenen Betrag zu begleichen.';
            } else {
                $subject = $invoice->getPrincipal()->getName().' | Beleg '.$document;
                $message = 'Anliegend erhalten Sie unseren Beleg mit der Belegnummer '.$document.' vom '.$invoice->getDate()->format('d.m.Y').'.';
            }
            $email->subject($subject);
            $email->text('Sehr geehrte Damen und Herren

'.$message.'

Mit freundlichen Grüssen

'.$principalFooter);
        } else {
            if($sendAsReminder) {
                $subject = $invoice->getPrincipal()->getName().' | Payment Reminder '.$document;
                $message = 'We would like to remind you that our invoice '.$document.' (date: '.$invoice->getDate()->format('Y-m-d').') is still due for payment and ask you to settle the outstanding amount.';
            } else {
                $subject = $invoice->getPrincipal()->getName().' | Document '.$document;
                $message = 'Please find enclosed the document '.$document.' (date: '.$invoice->getDate()->format('Y-m-d').').';
            }

            $email->subject($subject);
            $email->text('Dear Sir or Madam,

'.$message.'

Yours sincerely,

'.$principalFooter);
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

    public function cancel(Invoice $invoice): bool
    {
        if($invoice->getInvoiceType()->getType() != 'RE' || $invoice->isCancelled()) {
            $this->logger->info('Erstellung Rechnungskorrektur abgebrochen, ID #'.$invoice->getId().'.');
            return false;
        }

        $credit = new Invoice();
        $credit->setInvoiceReference($invoice);
        $credit->setInvoiceType($this->invoiceTypeRepository->findOneBy(['type'=>'RK']));
        $credit->setPrincipal($invoice->getPrincipal());
        $credit->setCustomer($invoice->getCustomer());
        $credit->setDate(new DateTimeImmutable());
        $credit->setNumber($this->buildInvoiceNumber((int)$credit->getPrincipal()->getFibuDocumentNumberRange()));
        $credit->setPeriodFrom($invoice->getPeriodFrom());
        $credit->setPeriodTo($invoice->getPeriodTo());
        $credit->setDue(new DateTimeImmutable());
        $credit->setVatType($invoice->getVatType());
        $credit->setVatRate($invoice->getVatRate());
        $credit->setAmountNet($invoice->getAmountNet());
        $credit->setAmountGross($invoice->getAmountGross());
        $credit->setCostcenterExternal($invoice->getCostcenterExternal());
        $credit->setReferenceExternal($invoice->getReferenceExternal());
        $credit->setNiceFilename($this->buildInvoiceNiceFilename($credit));
        $credit->setStorageFilename($this->buildInvoiceStorageFilename($credit));
        $credit->setCreatedAt(new DateTimeImmutable());
        $credit->setCreatedBy($this->security->getUser());
        $credit->setHCustomerName($credit->getCustomer()->getName());
        $credit->setHCustomerShortName($credit->getCustomer()->getShortName());
        $credit->setHPrincipalName($credit->getPrincipal()->getName());
        $credit->setHPrincipalShortName($credit->getPrincipal()->getShortName());
        $credit->setLanguage($invoice->getLanguage());
        $credit->setCurrency($invoice->getCurrency());
        $credit->setAccountingPlanLedger($invoice->getAccountingPlanLedger());
        $credit->setTermOfPayment($invoice->getTermOfPayment());
        $this->entityManager->persist($credit);

        $creditPosition = new InvoicePosition();
        $creditPosition->setText(($invoice->getLanguage()->getAlpha2() == 'DE' ? 'Gutschrift zur Rechnung' : 'Cancellation for invoice').' '.$invoice->getInvoiceType()->getType().' '.$invoice->getNumber());
        $creditPosition->setAmount(1);
        $creditPosition->setPrice($invoice->getAmountNet());
        $credit->addInvoicePosition($creditPosition);

        $invoice->setCancelled(true);
        $this->entityManager->persist($invoice);

        // PDF CREATION
        $pdfCreator = new InvoiceCreatePdfService($credit);

        $pdfCreator->addPdfHeaderAddress();
        $pdfCreator->addPdfHeaderInvoiceOverview();

        $pdfCreator->addPdfTableHeadRow();

        foreach($credit->getInvoicePositions() as $position) {
            $unit = '';
            $pdfCreator->addPdfTableBodyRow([
                $position->getText(),
                number_format($position->getAmount(), 2, ',', '.'),
                $unit,
                number_format($position->getPrice(), 2, ',', '.'),
                number_format($position->getPrice() * $position->getAmount(), 2, ',', '.')
            ]);
        }

        $pdfCreator->addPdfTableSumRows($credit->getAmountNet(), $credit->getVatRate(), $credit->getAmountGross());

        $pdfCreator->addPdfConditions([$this->getVatSentence($credit), $this->getPaymentSentence($credit), $credit->getOutroText()]);
        $pdfCreator->addPdfFooter();

        $pdf = $pdfCreator->getPdf();

        $pdf->Output('F', $this->buildFullPathToFile($credit->getStorageFilename()));
        unset($pdf);

        $this->sendToFibu($credit);

        $this->entityManager->flush();

        return true;
    }

}
