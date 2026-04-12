<?php
// ============================================================
// app/Exports/MonthlyReportExport.php
// ============================================================
namespace App\Exports;

use App\Models\Expense;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithTitle,
    WithStyles,
    WithColumnWidths,
    ShouldAutoSize,
    WithMapping,
};

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Border, Fill, Font};

class MonthlyReportExport implements
    FromCollection,
    WithHeadings,
    WithTitle,
    WithStyles,
    ShouldAutoSize,
    WithMapping
{
    public function __construct(
        private string $year,
        private string $month,
        private ?int   $projectId = null,
    ) {}

    // ── Données ────────────────────────────────────────────
    public function collection()
    {
        return Expense::with(['project', 'category', 'createdBy'])
            ->whereYear('expense_date', $this->year)
            ->whereMonth('expense_date', $this->month)
            ->when($this->projectId, fn($q) => $q->where('project_id', $this->projectId))
            ->orderBy('expense_date')
            ->get();
    }

    // ── Mapping colonnes ───────────────────────────────────
    public function map($expense): array
    {
        return [
            $expense->expense_date?->format('d/m/Y'),
            $expense->project?->name ?? '—',
            $expense->description,
            $expense->category?->name ?? '—',
            $expense->amount,
            match ($expense->status) {
                'validated' => 'Validée',
                'pending'   => 'En attente',
                'rejected'  => 'Rejetée',
                default     => $expense->status,
            },
            $expense->createdBy?->name ?? '—',
        ];
    }

    // ── En-têtes ───────────────────────────────────────────
    public function headings(): array
    {
        return [
            'Date',
            'Projet',
            'Description',
            'Catégorie',
            'Montant (FCFA)',
            'Statut',
            'Ajouté par',
        ];
    }

    // ── Titre de l'onglet ──────────────────────────────────
    public function title(): string
    {
        return Carbon::createFromDate($this->year, $this->month, 1)
            ->translatedFormat('F Y');
    }

    // ── Styles ─────────────────────────────────────────────
    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // En-tête
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0053DB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Lignes alternées
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0F4F7']],
                ]);
            }
        }

        // Colonne montant : alignement droite + format monétaire
        $sheet->getStyle("E2:E{$lastRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);
        $sheet->getStyle("E2:E{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0 "FCFA"');

        // Bordures légères
        $sheet->getStyle("A1:G{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFE2E8F0'],
                ],
            ],
        ]);

        // Ligne de total
        $totalRow = $lastRow + 2;
        $sheet->setCellValue("D{$totalRow}", 'TOTAL');
        $sheet->setCellValue("E{$totalRow}", "=SUM(E2:E{$lastRow})");
        $sheet->getStyle("D{$totalRow}:E{$totalRow}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDBE1FF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);
        $sheet->getStyle("E{$totalRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0 "FCFA"');

        // Hauteur ligne entête
        $sheet->getRowDimension(1)->setRowHeight(22);

        return [];
    }
}
