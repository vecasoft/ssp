<?php

require($_SESSION["SX_BASEPATH"] . '/tcpdf/tcpdf.php');

class UitgaandeFactuur extends TCPDF {

    public $__currentY = 0;

    public $documentType;

    public $logoVoetbal;
    public $logoTennis;

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
    public $mededelingen;
    public $totalen;

    public $totaalMaatstaf;
    public $totaalBTW;
    public $totaalTotaal;

    public $reedsBetaald;
    public $teBetalen;

    public $GM;
    public $IBAN;
    public $vervalDatum;

    public $isCreditnota;

    function Header() {

        $this->Image($this->logoVoetbal, 5, 5, 20, '', 'JPG', '', 'T', true, 300, '', false, false, 0, false, false, false);
        $this->Image($this->logoTennis, 180, 5, 20, '', 'JPG', '', 'T', true, 300, '', false, false, 0, false, false, false);

        $this->SetXY(34,5);
        $this->SetFillColor(255, 0, 0);
        $this->SetFont('helvetica','B',12);
        $this->Cell(90,0,"K. SCHELLLE SPORT vzw",0,0,"",false);

        $this->SetXY(34,11);
        $this->SetFont('helvetica','',12);
        $this->Cell(90,0,"Sportcomplex / Maatsch. zetel: Kapelstraat 140 - 2627 Schelle",0,0,"",false);

        $this->SetXY(34,17);
        $this->Write(0, 'Tel: 03 887 29 80   ', '', false, 'L', false);
        $this->Write(0, 'Mail: info@schellesport.be', 'mailto: info@schellesport.be', false, 'L', false);
        $this->Write(0, '   ', '', false, 'L', false);
        $this->Write(0, 'Web: www.schellesport.be', 'https://www.schellesport.be', false, 'L', true);

        $this->SetXY(34,23);
        $this->Cell(90,0,"btw: BE 0417 639 834",0,0,"",false);

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
        $titel = 'Vervaldatum:';
        $this->SetXY(5,$line);
        $this->Cell(35,0,$titel);

        if (! $this->isCreditnota)
            $this->Cell(25,0,"$this->vervalDatum");

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

        $html = "<table border=\"1\" cellpadding=\"6\"  align=\"right\"><thead><tr style=\"background-color:#CEEBFD;\"><th  align=\"left\" style=\"width: 210px\" >Item</th><th>Maatstaf BTW</th><th style=\"width: 60px\">BTW %</th><th style=\"width: 80px\">BTW</th><th style=\"width: 80px\">Totaal</th></tr></thead>";

        $btwPercentages = $this->btwPercentages;

        $aantalBtwEntries = 0;

        foreach ($btwPercentages as $key => $btwPercentage){

            $aantalBtwEntries += 1;

            $omschrijving = $this->mededelingen[$key];
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

        if ($this->reedsBetaald){

            $y = $this->GetY() - 5;
            $this->SetY($y);

            $this->SetX(5);
            $titel = 'Reeds betaald:';
            $this->Cell(35, 0, $titel);
            $this->Cell(25, 0, "$this->reedsBetaald");
            $this->Ln(8);

        }

        $this->SetX(5);

        if ($this->isCreditnota)
            $titel = 'Totaal bedrag:';
        else
            $titel = 'Te betalen:';

        $this->Cell(35, 0, $titel);
        $this->SetFont('helvetica','B',12);
                $this->Cell(25, 0, "$this->teBetalen");

        $this->SetFont('helvetica','',12);
        $this->Ln(5);

        $y = $this->GetY();

        if ($this->isCreditnota){
            $betaalInstructie1 = "BTW terug te storten aan de staat in de mate waarin ze oorspronkelijk in aftrek";
            $betaalInstructie2 = "gebracht werd.";
        } else {
            $betaalInstructie1 = "Gelieve het verschuldigde bedrag te betalen op IBAN <b>$this->IBAN</b> van Schelle Sport";
            $betaalInstructie2 = "Met gestructureerde mededeling: <b>$this->GM</b>";
        }

        $this->SetXY(5, $y + 5);
        $html = "<table cellpadding=\"6\" style=\"background-color:#CEEBFD;\"><tr><td>$betaalInstructie1</td></tr>";
        $html .= "<tr><td>$betaalInstructie2</td></tr></table>";
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
        $this->Cell(15,10,"EFIN - Eenvoudige Financiële Administratie");
        // Page number
         $this->Cell(0,10,'Blz '.$this->PageNo() . ' / ' . $this->getAliasNbPages(),0,0,'R');

    }
}

?>