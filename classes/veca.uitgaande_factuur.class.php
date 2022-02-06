<?php

require($_SESSION["SX_BASEPATH"] . '/tcpdf/tcpdf.php');

class UitgaandeFactuur extends TCPDF {

    public $__currentY = 0;

    public $documentType;

    public $logoVECA;

    public $documentTitel;
    public $labelFactuurnummer;
    public $labelFactuurdatum;

    public $factuurDatum;
    public $factuurNummer;
    public $klantNaam;
    public $klantAdres;
    public $klantGemeente;
    public $klantLand;
    public $klantBTWnr;
    public $pageType;

    public $omschrijving;
    public $maatstaffen;
    public $btwPercentages;
    public $btwBedragen;
    public $omschrijvingen;
    public $totalen;

    public $totaalMaatstaf;
    public $totaalBTW;
    public $totaalTotaal;

    public $reedsBetaald;
    public $teBetalen;

    public $GM;
    public $IBAN;
    public $vervalDatum;

    function Header() {

        $this->Image($this->logoVECA, 5, 5, 20, '', 'PNG', '', 'T', true, 300, '', false, false, 0, false, false, false);

        $this->SetXY(34,5);
        $this->SetFillColor(255, 0, 0);
        $this->SetFont('helvetica','B',14);
        $this->Cell(90,0,"VECA Software bv",0,0,"",false);

        $this->SetXY(34,11);
        $this->SetFont('helvetica','',12);
        $this->Cell(90,0,"Maatsch. zetel: Molendreef 20 / 7 - 2620 Hemiksem",0,0,"",false);

        $this->SetXY(34,17);
        $this->Write(0, 'Tel: +32 475 82 62 01   ', '', false, 'L', false);
        $this->Write(0, 'Mail: info@vecasoftware.com', 'mailto: info@vecasoftware.com', false, 'L', false);
        $this->Write(0, '   ', '', false, 'L', false);
        $this->Write(0, 'Web: www.vecasoftware.com', 'https://www.vecasoftware.com', false, 'L', true);

        $this->SetXY(34,23);
        $this->Cell(90,0,"btw: BE 0466 935 531  RPR Antwerpen",0,0,"",false);

    }

    function FactuurBody() {

        $this->SetXY(75,45);
        $this->SetFillColor(255, 0, 0);
        $this->SetFont('helvetica','B',20);
        $this->Cell(90,0,$this->documentTitel,0,0,"",false);

        $this->SetFont('helvetica','',12);
        $this->SetTextColor(0, 0, 0);

        $line = 60;

        $this->SetFont('helvetica','B',12);
        $this->SetXY(125,$line);
        $this->Cell(55,0,$this->klantNaam);
        $this->Ln(5);
        $this->SetFont('helvetica','',12);

        $line += 12;
        $this->SetXY(125,$line);
        $this->Cell(55,0,$this->klantAdres);
        $this->Ln(5);
        $line += 6;
        $this->SetXY(125,$line);
        $this->Cell(55,0,$this->klantGemeente);
        $this->Ln(5);
        $line += 6;
        $this->SetXY(125,$line);
        $this->Cell(55,0,$this->klantLand);
        $this->Ln(5);
        $line += 8;
        $this->SetXY(125,$line);
        $this->Cell(55,0,"btw: $this->klantBTWnr");
        $this->Ln(5);

        $line += 6;
        $titel = $this->labelFactuurnummer;
        $this->SetXY(5,$line);
        $this->Cell(35,0,$titel);
        $this->Cell(25,0,"$this->factuurNummer");
        $this->Ln(5);

        $line += 6;
        $titel = $this->labelFactuurdatum;
        $this->SetXY(5,$line);
        $this->Cell(35,0,$titel);
        $this->Cell(25,0,"$this->factuurDatum");
        $this->Ln(5);

        $line += 6;
        $this->SetXY(5,$line);
        $this->Cell(190,0,"",'B','C',1,0);

        $line += 12;
        $this->SetXY(5,$line);
        $titel = 'Omschrijving:';
        $this->Cell(35,0,$titel);
        $this->Cell(25,0,"$this->omschrijving");
        $this->Ln(5);

        $line += 12;
        $this->SetXY(5, $line);

        $html = "<table border=\"1\" cellpadding=\"6\"  align=\"right\"><thead><tr style=\"background-color:#CEEBFD;\"><th  align=\"left\" style=\"width: 210px\">Item</th><th>Maatstaf BTW</th><th style=\"width: 60px\">BTW %</th><th style=\"width: 80px\">BTW</th><th style=\"width: 80px\">Totaal</th></tr></thead>";

        $btwPercentages = $this->btwPercentages;

        $aantalBtwEntries = 0;

        foreach ($btwPercentages as $key => $btwPercentage){

            $aantalBtwEntries += 1;

            $omschrijving = $this->omschrijvingen[$key];
            $maatstaf = $this->maatstaffen[$key];
            $btwPercentage = $this->btwPercentages[$key];
            $btwBedrag = $this->btwBedragen[$key];
            $totaal = $this->totalen[$key];

            $html .= "<tr ><td  align=\"left\" style=\"width: 210px\">$omschrijving</td><td>$maatstaf</td><td style=\"width: 60px\">$btwPercentage</td><td style=\"width: 80px\">$btwBedrag</td><td style=\"width: 80px\"><b>$totaal</b></td></tr>";

        }

        if ($aantalBtwEntries > 1)
            $html .= "<tr  style=\"background-color:#CEEBFD;\"><td  align=\"left\" style=\"width: 210px\">Totalen</td><td>$this->totaalMaatstaf</td><td style=\"width: 60px\"></td><td style=\"width: 80px\">$this->totaalBTW</td><td style=\"width: 80px\"><b>$this->totaalTotaal</b></td></tr>";

        $html .= "</table>";

        $this->writeHTML($html, true, false, true, false, '');

        $this->SetX(5);
        $titel = 'Te betalen:';
        $this->Cell(35, 0, $titel);
        $this->SetFont('helvetica','B',12);
        $this->Cell(25, 0, "$this->teBetalen");
        $this->SetFont('helvetica','',12);
        $this->Ln(5);

        $y = $this->GetY();
        $betaalInstructie1 = "Gelieve het bedrag te betalen op IBAN <b>BE33 0682 2479 9446</b> van VECA Software bv, ";
        $betaalInstructie2 = "met vermelding van het factuurnummer.";
        $this->SetXY(5, $y + 5);
        $html = "<table cellpadding=\"6\" style=\"background-color:#CEEBFD;\"><tr><td>$betaalInstructie1<br/>$betaalInstructie2</td></tr>";
        // $html .= "<tr><td>Met vermelding van het factuurnummer.</td></tr>";
        $html .= "</table>";
        $this->writeHTML($html, true, false, true, false, '');

        $this->__currentY = $this->GetY();

    }


    function ExtraOmschrijving($pExtraOmschrijving, $pY = 0) {

        $x = 5;

        if ( ! $pY)
         $y = $this->__currentY -5;
        else
            $y = $pY;

        $this->SetXY($x,$y);
        $this->Cell(190,0,"Extra Info",'B','C',1,0);

        $y += 10;
        $this->SetXY($x,$y);
        $this->SetFont('','I',10);
        $this->MultiCell(800, 0, $pExtraOmschrijving);

        $this->__currentY = $this->GetY();

        return $this->__currentY;

    }

    function Footer() {

        $this->SetY(-20);
        $this->SetX(5);
        $this->Cell(190,0,"",'B','C',1,0);

        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        //  italic 8
        $this->SetFont('','',8);
        // Text color in gray
        $this->SetTextColor(128);
        $this->SetX(5);
        // Page number
         $this->Cell(0,10,'Blz '.$this->PageNo() . ' / ' . $this->getAliasNbPages(),0,0,'R');

    }
}

?>