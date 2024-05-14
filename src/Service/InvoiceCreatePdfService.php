<?php

namespace App\Service;

use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\Principal;
use Fpdf\Fpdf;

class InvoiceCreatePdfService
{

    private Fpdf $pdf;

    private string $lang;
    private string $currencySymbol;
    private string $currencyName;
    private Customer $receiver;
    private Principal $principal;
    private Invoice $invoice;

    private int $positionsCounter = 0;

    private const WIDTHS = [95, 20, 23, 20, 20];
    private const TABLEHEADS_DE = ['Beschreibung', 'Menge', 'Einheit', 'Preis [_CURRENCY_]', 'Gesamt [_CURRENCY_]'];
    private const TABLEHEADS_EN = ['Position', 'Quantity', 'Unit', 'Price [_CURRENCY_]', 'Total [_CURRENCY_]'];

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->principal = $invoice->getPrincipal();
        $this->receiver = $invoice->getCustomer();
        $this->lang = (($this->invoice->getLanguage()->getAlpha2() == 'DE' || $this->invoice->getLanguage()->getAlpha2() == 'CH') ? 'DE' : 'EN');
        $this->currencySymbol = ($invoice->getCurrency()->getAlpha3() == 'EUR' ? chr(128).' ' : $invoice->getCurrency()->getSymbol());
        $this->currencyName = $invoice->getCurrency()->getName();

        $this->pdf = new Fpdf('P', 'mm', 'A4');
        $this->pdf->SetMargins(17, 5);
        $this->pdf->SetAutoPageBreak(false);
        $this->pdf->AliasNbPages();
        $this->pdf->AddPage();
    }

    public function getPdf(): Fpdf
    {
        return $this->pdf;
    }

    public function addPdfHeaderAddress(): void
    {
        if($this->principal->getLogoPath() != null) {
            $x = 145;
            $y = 10;

            // TODO: Logo-Position muss mit jedem Logo funktionieren & korrekt sein!
            if($this->principal->getLogoPath() == 'Q.png') {
                $x = 172.5;
                $y = 13;
            } elseif($this->principal->getLogoPath() == 'ThinkingArabian-Full.png') {
                $x = 154;
            }

            $this->pdf->Image('images/logos/'.$this->principal->getLogoPath(), $x, $y, 0, 20);
        }

        $this->pdf->SetY(35);
        
        $principalAddress = [$this->principal->getName()];
        if($this->principal->getAddressLine1())
            $principalAddress[] = $this->principal->getAddressLine1();
        if($this->principal->getAddressLine2())
            $principalAddress[] = $this->principal->getAddressLine2();
        if($this->principal->getAddressLine3())
            $principalAddress[] = $this->principal->getAddressLine3();
        if($this->principal->getAddressLine4())
            $principalAddress[] = $this->principal->getAddressLine4();
        if($this->principal->getAddressLineCountry())
            $principalAddress[] = $this->principal->getAddressLineCountry()->getName();
        $principalAddressString = implode(' | ', $principalAddress);
        
        $this->pdf->SetFont('Arial', '', 7);
        $this->pdf->Cell(123, 5, utf8_decode($principalAddressString));
        $this->pdf->Ln();

        $receiverAddress = [$this->receiver->getName()];
        if($this->receiver->getAddressLine1())
            $receiverAddress[] = $this->receiver->getAddressLine1();
        if($this->receiver->getAddressLine2())
            $receiverAddress[] = $this->receiver->getAddressLine2();
        if($this->receiver->getAddressLine3())
            $receiverAddress[] = $this->receiver->getAddressLine3();
        if($this->receiver->getAddressLine4())
            $receiverAddress[] = $this->receiver->getAddressLine4();
        if($this->receiver->getAddressLineCountry())
            $receiverAddress[] = $this->receiver->getAddressLineCountry()->getName();
        $receiverAddressString = implode('
', $receiverAddress);

        $y = $this->pdf->GetY(); // zu Beginn
        $numberOfLinesAtAddress = count($receiverAddress);
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->MultiCell(123, 5, utf8_decode($receiverAddressString));
        $this->pdf->Ln();

        // Richtige Position finden & Abstand
        $regular_y = $this->pdf->GetY();
        $according_to_lines_y = $y + ($numberOfLinesAtAddress * 5);
        $new_y = max($regular_y, $according_to_lines_y); // je nachdem ob rechts mit eigenen Daten oder links mit Adresse länger ist
        $this->pdf->SetY($new_y + 18);
    }

    public function addPdfHeaderInvoiceOverview(): void
    {
        $cell1 = 40;
        $cell2 = 55;
        $cell3 = $cell1;
        $cell4 = $cell2;
        $height = 5;

        // Überschrift
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->Cell($cell1+$cell2, 5, ($this->lang == 'DE' ? $this->invoice->getInvoiceType()->getName() : $this->invoice->getInvoiceType()->getName()));
        $this->pdf->Ln();

        // Belegdatum (links) + Kundennummer (rechts)
        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell($cell1, $height, ($this->lang == 'DE' ? 'Belegdatum' : 'Document Date').':');
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->Cell($cell2, $height, $this->invoice->getDate()->format('d.m.Y'));
        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell($cell3, $height, ($this->lang == 'DE' ? 'Ihre Kundennummer' : 'Your Customer Id').':');
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->Cell($cell4, $height, $this->receiver->getLedgerAccountNumber());
        $this->pdf->Ln();

        // Belegnummer (links) + Externe Kostenstelle (rechts)
        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell($cell1, $height, ($this->lang == 'DE' ? 'Belegnummer' : 'Document Number').':');
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->Cell($cell2, $height, $this->invoice->getInvoiceType()->getType().' '.($this->invoice->getNumber() == 99999999 ? ($this->lang == 'DE' ? 'ENTWURF' : 'DRAFT') : $this->invoice->getNumber()));
        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell($cell3, $height, ($this->lang == 'DE' ? 'Ihre Kostenstelle' : 'Your Cost Center').':');
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->Cell($cell4, $height, $this->invoice->getCostcenterExternal());
        $this->pdf->Ln();

        // Leistungszeitraum (links) + Externe Referenz (rechts)
        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell($cell1, $height, ($this->lang == 'DE' ? 'Leistungszeitraum' : 'Period of Performance').':');
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->Cell($cell2, $height, $this->invoice->getPeriodFrom()->format('d.m.Y').' - '.$this->invoice->getPeriodTo()->format('d.m.Y'));
        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell($cell3, $height, ($this->lang == 'DE' ? 'Ihre Referenz' : 'Your Reference').':');
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->Cell($cell4, $height, $this->invoice->getReferenceExternal());
        $this->pdf->Ln();

        // Seite x von y (links) + Externe USt-IdNr. (rechts - wenn vorhanden)
        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell($cell1, 5, ($this->lang == 'DE' ? 'Seite:' : 'Page:'));
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->Cell($cell2, 5, $this->pdf->PageNo().' '.($this->lang == 'DE' ? 'von' : 'of').' {nb}');
        if($this->receiver->getVatId() != null) {
            $this->pdf->SetFont('Arial', 'B', 9);
            $this->pdf->Cell($cell3, $height, ($this->lang == 'DE' ? 'Ihre USt-IdNr.' : 'Your VAT ID').':');
            $this->pdf->SetFont('Arial', '', 9);
            $this->pdf->Cell($cell4, $height, $this->receiver->getVatId());
        }
        $this->pdf->Ln();

        if($this->invoice->getIntroText() != null) {
            // Abstand
            $this->pdf->SetY($this->pdf->GetY() + 6);

            // Kontext (links)
            $this->pdf->SetFont('Arial', '', 9);
            $this->pdf->MultiCell(0, 4, utf8_decode($this->invoice->getIntroText()));
            $this->pdf->Ln();
        }

        // Abstand
        $this->pdf->SetY($this->pdf->GetY() + 6);
    }

    public function addPdfTableHeadRow(): void
    {
        $this->pdf->SetFont('Arial', 'B', 9);
        $i = 0;
        foreach(($this->lang == 'DE' ? self::TABLEHEADS_DE : self::TABLEHEADS_EN) as $value)
            $this->pdf->Cell(self::WIDTHS[$i++], 5, str_replace('_CURRENCY_', $this->currencySymbol, $value), 'B', 0, 'L');
        $this->pdf->Ln();
    }

    public function addPdfTableBodyRow(array $values): void
    {
        $this->pdf->SetFont('Arial', '', 9);
        if($this->positionsCounter != 0)
            $this->pdf->Ln(3);
        $yAfterMultiCell = $this->pdf->GetY(); // just as fallback, never required or used
        $i = 0;
        foreach($values as $value) {
            $align = (in_array($i, [0,2]) ? 'L' : 'R');
            if($i == 0) {
                $xBeforeMultiCell = $this->pdf->GetX();
                $yBeforeMultiCell = $this->pdf->GetY();
                $this->pdf->MultiCell(self::WIDTHS[$i], 5, utf8_decode($value), 0, $align);
                $yAfterMultiCell = $this->pdf->GetY();
                $this->pdf->SetXY($xBeforeMultiCell+self::WIDTHS[$i], $yBeforeMultiCell);
            } else {
                $this->pdf->Cell(self::WIDTHS[$i], 5, utf8_decode($value), '0', 0, $align);
            }
            $i++;
        }
        $this->pdf->SetY($yAfterMultiCell-5);
        $this->pdf->Ln();
        $this->positionsCounter++;

        if($this->positionsCounter == 5) {
            $this->addPdfFooter();

            $this->pdf->AddPage();
            $this->pdf->SetY($this->pdf->GetY() + 15);

            $this->addPdfHeaderInvoiceOverview();
            $this->addPdfTableHeadRow();

            $this->positionsCounter = 0;
        }
    }

    private function addPdfTableBodyElement($width, $value, $orientation = 'L', $bold = false, $borderTop = false): void
    {
        $this->pdf->SetFont('Arial', ($bold ? 'B' : ''), 9);
        $this->pdf->Cell($width, 4.5, ($value), ($borderTop ? 'T' : '0'), '0', $orientation);
    }

    public function addPdfTableSumRows(float $sumNet, float $taxRate, float $sumGross): void
    {
        $width_a = self::WIDTHS[0] + self::WIDTHS[1] + self::WIDTHS[2] + self::WIDTHS[3];

        // Summenzeile Netto
        $this->addPdfTableBodyElement($width_a, ($this->lang == 'DE' ? 'Gesamt netto' : 'Total net').':', 'R', true, true);
        $this->addPdfTableBodyElement(self::WIDTHS[4], number_format($sumNet, 2, ',', '.'), 'R', true, true);
        $this->pdf->Ln();

        // Summenzeile Steuer
        $this->addPdfTableBodyElement($width_a, ($this->lang == 'DE' ? 'USt.' : 'Tax').' '.number_format($taxRate, 2, ',', '.').' %:', 'R', true);
        $this->addPdfTableBodyElement(self::WIDTHS[4], number_format($sumNet * $taxRate / 100, 2, ',', '.'), 'R', true);
        $this->pdf->Ln();

        // Summenzeile Brutto
        $this->addPdfTableBodyElement($width_a, ($this->lang == 'DE' ? 'Gesamt brutto' : 'Total gross').':', 'R', true);
        $this->addPdfTableBodyElement(self::WIDTHS[4], number_format($sumGross, 2, ',', '.'), 'R', true);
        $this->pdf->Ln();
    }

    public function addPdfConditions(array $conditions): void
    {
        $this->pdf->Ln();
        foreach($conditions AS $condition) {
            $this->pdf->SetFont('Arial', '', 9);
            $this->pdf->MultiCell(0, 4, utf8_decode(str_replace('_CURRENCY_', $this->currencyName.' ('.utf8_encode($this->currencySymbol).')', $condition)), 0, 'L');
            $this->pdf->Ln();
        }
    }

    public function addPdfFooter(): void
    {
        $this->pdf->SetY(-25);
        $this->pdf->SetFont('Arial', '', 7);
//        $this->pdf->SetTextColor(108, 117, 125);

        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();

        $footerColumn1 = null;
        if($this->receiver->getSpecialFooterColumn1())
            $footerColumn1 = $this->receiver->getSpecialFooterColumn1();
        elseif($this->lang == 'DE')
            $footerColumn1 = $this->principal->getFooterColumn1();
        else
            $footerColumn1 = $this->principal->getFooterColumn1En();
        $this->pdf->MultiCell(60, 3, utf8_decode($footerColumn1), 'T');

        $x2 = $x + 60;
        $this->pdf->SetXY($x2, $y);

        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();

        $footerColumn2 = null;
        if($this->receiver->getSpecialFooterColumn2())
            $footerColumn2 = $this->receiver->getSpecialFooterColumn2();
        elseif($this->lang == 'DE')
            $footerColumn2 = $this->principal->getFooterColumn2();
        else
            $footerColumn2 = $this->principal->getFooterColumn2En();
        $this->pdf->MultiCell(60, 3, utf8_decode($footerColumn2), 'T');

        $x2 = $x + 60;
        $this->pdf->SetXY($x2, $y);

        $footerColumn3 = null;
        if($this->receiver->getSpecialFooterColumn3())
            $footerColumn3 = $this->receiver->getSpecialFooterColumn3();
        elseif($this->lang == 'DE')
            $footerColumn3 = $this->principal->getFooterColumn3();
        else
            $footerColumn3 = $this->principal->getFooterColumn3En();
        $this->pdf->MultiCell(60, 3, utf8_decode($footerColumn3), 'T');

        $this->pdf->SetTextColor(0, 0, 0);
    }

}