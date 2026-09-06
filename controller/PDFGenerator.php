<?php
/**
 * Générateur de PDF simple sans dépendance externe
 * Utilise FPDF - bibliothèque PHP pure
 */

class PDFGenerator
{
    /**
     * Générer le PDF d'une ordonnance
     */
    public static function generateOrdonnancePDF(array $ordonnance): string
    {
        // Inclure FPDF si disponible, sinon utiliser une version simplifiée
        if (!class_exists('FPDF')) {
            self::includeFPDF();
        }
        
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 15);
        
        // En-tête
        self::addHeader($pdf, $ordonnance);
        
        // Informations ordonnance
        self::addOrdonnanceInfo($pdf, $ordonnance);
        
        // Liste des médicaments
        self::addMedicamentsList($pdf, $ordonnance);
        
        // Pied de page
        self::addFooter($pdf, $ordonnance);
        
        // Générer le nom de fichier
        $filename = 'ordonnance_' . $ordonnance['numero_ordonnance'] . '_' . date('Ymd_His') . '.pdf';
        $filepath = __DIR__ . '/../view/uploads/pdf/' . $filename;
        
        // Créer le dossier si nécessaire
        if (!is_dir(__DIR__ . '/../view/uploads/pdf/')) {
            mkdir(__DIR__ . '/../view/uploads/pdf/', 0755, true);
        }
        
        // Sauvegarder le PDF
        $pdf->Output('F', $filepath);
        
        return 'view/uploads/pdf/' . $filename;
    }
    
    /**
     * Inclure FPDF (version simplifiée intégrée)
     */
    private static function includeFPDF(): void
    {
        $fpdfPath = __DIR__ . '/../view/vendor/fpdf/fpdf.php';
        
        if (file_exists($fpdfPath)) {
            require_once $fpdfPath;
        } else {
            // Utiliser une classe de fallback si FPDF n'est pas disponible
            self::createFallbackPDF();
        }
    }
    
    /**
     * Ajouter l'en-tête du PDF
     */
    private static function addHeader($pdf, $ordonnance): void
    {
        // Logo/Titre
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->SetTextColor(102, 126, 234);
        $pdf->Cell(0, 10, utf8_decode('🏥 PHARMACIE - ORDONNANCE'), 0, 1, 'C');
        
        $pdf->Ln(5);
        
        // Ligne de séparation
        $pdf->SetDrawColor(102, 126, 234);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        
        $pdf->Ln(8);
    }
    
    /**
     * Ajouter les informations de l'ordonnance
     */
    private static function addOrdonnanceInfo($pdf, $ordonnance): void
    {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, utf8_decode('INFORMATIONS ORDONNANCE'), 0, 1);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->Ln(2);
        
        // Cadre d'informations
        $pdf->SetFillColor(248, 249, 250);
        $pdf->Rect(10, $pdf->GetY(), 190, 45, 'F');
        
        $y = $pdf->GetY() + 5;
        $pdf->SetXY(15, $y);
        
        // Colonne gauche
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(50, 6, utf8_decode('Numéro :'), 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(60, 6, utf8_decode($ordonnance['numero_ordonnance'] ?? 'N/A'), 0, 1);
        
        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(50, 6, utf8_decode('Date prescription :'), 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(60, 6, date('d/m/Y', strtotime($ordonnance['date_prescription'])), 0, 1);
        
        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(50, 6, utf8_decode('Médecin :'), 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(60, 6, utf8_decode($ordonnance['nom_medecin'] ?? 'N/A'), 0, 1);
        
        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(50, 6, utf8_decode('Patient :'), 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $clientNom = ($ordonnance['client_nom'] ?? '') . ' ' . ($ordonnance['client_prenom'] ?? '');
        $pdf->Cell(60, 6, utf8_decode($clientNom), 0, 1);
        
        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(50, 6, utf8_decode('Statut :'), 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $statut = strtoupper($ordonnance['statut'] ?? 'N/A');
        $pdf->Cell(60, 6, utf8_decode($statut), 0, 1);
        
        $pdf->Ln(10);
    }
    
    /**
     * Ajouter la liste des médicaments
     */
    private static function addMedicamentsList($pdf, $ordonnance): void
    {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('MÉDICAMENTS PRESCRITS'), 0, 1);
        $pdf->Ln(2);
        
        // En-tête du tableau
        $pdf->SetFillColor(102, 126, 234);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 9);
        
        $pdf->Cell(70, 8, utf8_decode('Médicament'), 1, 0, 'C', true);
        $pdf->Cell(25, 8, utf8_decode('Quantité'), 1, 0, 'C', true);
        $pdf->Cell(30, 8, utf8_decode('Prix Unit.'), 1, 0, 'C', true);
        $pdf->Cell(30, 8, utf8_decode('Total'), 1, 0, 'C', true);
        $pdf->Cell(35, 8, utf8_decode('Durée'), 1, 1, 'C', true);
        
        // Lignes du tableau
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 9);
        
        $total = 0;
        $medicaments = $ordonnance['medicaments'] ?? [];
        
        foreach ($medicaments as $index => $med) {
            $fill = ($index % 2 == 0);
            if ($fill) {
                $pdf->SetFillColor(248, 249, 250);
            }
            
            $nomMed = $med['nom_commercial'] ?? 'N/A';
            $quantite = $med['quantite'] ?? 0;
            $prixUnit = $med['prix_unitaire_vente'] ?? 0;
            $sousTotal = $quantite * $prixUnit;
            $duree = $med['duree_traitement'] ?? 'N/A';
            
            $total += $sousTotal;
            
            // Gérer les noms longs
            if (strlen($nomMed) > 35) {
                $nomMed = substr($nomMed, 0, 32) . '...';
            }
            
            $pdf->Cell(70, 7, utf8_decode($nomMed), 1, 0, 'L', $fill);
            $pdf->Cell(25, 7, $quantite, 1, 0, 'C', $fill);
            $pdf->Cell(30, 7, number_format($prixUnit, 2) . ' EUR', 1, 0, 'R', $fill);
            $pdf->Cell(30, 7, number_format($sousTotal, 2) . ' EUR', 1, 0, 'R', $fill);
            $pdf->Cell(35, 7, utf8_decode($duree), 1, 1, 'C', $fill);
            
            // Posologie (ligne supplémentaire)
            if (!empty($med['posologie_prescrite'])) {
                $pdf->SetFont('Arial', 'I', 8);
                $pdf->SetTextColor(100, 100, 100);
                $pdf->Cell(10, 5, '', 0, 0);
                $posologie = $med['posologie_prescrite'];
                if (strlen($posologie) > 90) {
                    $posologie = substr($posologie, 0, 87) . '...';
                }
                $pdf->Cell(0, 5, utf8_decode('Posologie: ' . $posologie), 0, 1);
                $pdf->SetFont('Arial', '', 9);
                $pdf->SetTextColor(0, 0, 0);
            }
        }
        
        // Total
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(102, 126, 234);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(125, 8, utf8_decode('TOTAL'), 1, 0, 'R', true);
        $pdf->Cell(30, 8, number_format($total, 2) . ' EUR', 1, 0, 'R', true);
        $pdf->Cell(35, 8, '', 1, 1, 'C', true);
        
        $pdf->Ln(5);
    }
    
    /**
     * Ajouter le pied de page
     */
    private static function addFooter($pdf, $ordonnance): void
    {
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetFont('Arial', 'I', 8);
        
        // Informations complémentaires
        if (!empty($ordonnance['commentaire_pharmacien'])) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(0, 6, utf8_decode('Notes du pharmacien :'), 0, 1);
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->MultiCell(0, 5, utf8_decode($ordonnance['commentaire_pharmacien']));
            $pdf->Ln(3);
        }
        
        // Ligne de séparation
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(5);
        
        // Informations légales
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->Cell(0, 4, utf8_decode('Document généré le ' . date('d/m/Y à H:i')), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode('Système de Gestion de Pharmacie - PHP 8 MVC'), 0, 1, 'C');
        
        // Numéro de page
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, utf8_decode('Page ' . $pdf->PageNo()), 0, 0, 'C');
    }
    
    /**
     * Créer une classe de fallback si FPDF n'est pas disponible
     */
    private static function createFallbackPDF(): void
    {
        // Message d'erreur si FPDF n'est pas installé
        throw new Exception("FPDF n'est pas installé. Veuillez télécharger FPDF depuis http://www.fpdf.org/ et le placer dans view/vendor/fpdf/");
    }
    
    /**
     * Télécharger le PDF directement
     */
    public static function downloadOrdonnancePDF(array $ordonnance): void
    {
        if (!class_exists('FPDF')) {
            self::includeFPDF();
        }
        
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 15);
        
        self::addHeader($pdf, $ordonnance);
        self::addOrdonnanceInfo($pdf, $ordonnance);
        self::addMedicamentsList($pdf, $ordonnance);
        self::addFooter($pdf, $ordonnance);
        
        $filename = 'ordonnance_' . $ordonnance['numero_ordonnance'] . '.pdf';
        
        // Téléchargement direct
        $pdf->Output('D', $filename);
    }
}
?>
