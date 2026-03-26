<?php
require_once __DIR__ . '/_inc.php';
require_admin();
$admin_username = $_SESSION['admin_user'] ?? 'Admin';
$page_title = 'Cost Calculator';
$flash = function_exists('flash_get') ? flash_get() : null;

$edit_record = null;
$edit_summary = [];
$edit_details = [];
$edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;

if ($edit_id > 0 && isset($conn) && ($conn instanceof mysqli)) {
    $tableCheck = $conn->query("SHOW TABLES LIKE 'cost_calculator_records'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $stmt = $conn->prepare("SELECT * FROM cost_calculator_records WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $edit_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $edit_record = $res ? $res->fetch_assoc() : null;
            $stmt->close();
        }

        if ($edit_record) {
            $decodedSummary = json_decode((string)($edit_record['summary_payload'] ?? ''), true);
            $decodedDetails = json_decode((string)($edit_record['calculation_payload'] ?? ''), true);
            $edit_summary = is_array($decodedSummary) ? $decodedSummary : [];
            $edit_details = is_array($decodedDetails) ? $decodedDetails : [];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> — ACE Admin</title>
    <link rel="stylesheet" href="/ACE/User/front.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --accent: #6f42c1; --accent-dark: #5a32a3; }
        *, *::before, *::after { box-sizing: border-box; }
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .calculator-container { max-width: 1200px; margin: 30px auto; padding: 20px 15px; }
        .section-card { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; overflow: hidden; border: 1px solid #ececec; }
        .section-header {
            background: var(--accent);
            color: white;
            padding: 15px 20px;
            font-weight: bold;
            letter-spacing: 0.2px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
        }
        .section-header:hover { background: var(--accent-dark); }
        .section-header .toggle-icon {
            font-size: 0.85rem;
            transform: rotate(-90deg);
            transition: transform 0.2s ease;
        }
        .section-card.open .section-header .toggle-icon { transform: rotate(0deg); }
        .section-body { padding: 20px; display: block; }
        .section-card.collapsible .section-body { display: none; }
        .section-card.collapsible.open .section-body { display: block; }
        .input-row { display: grid; grid-template-columns: minmax(170px, 2fr) repeat(3, minmax(85px, 1fr)) minmax(110px, 1fr); gap: 10px; margin-bottom: 10px; align-items: center; }
        .input-row > span { grid-column: 1; font-weight: 500; }
        .input-row .rate { grid-column: 2; }
        .input-row .days { grid-column: 3; }
        .input-row .staff { grid-column: 4; }
        .input-row .total-field { grid-column: 5; }
        .input-row.direct-row { grid-template-columns: minmax(170px, 2fr) minmax(110px, 1fr) minmax(110px, 1fr); }
        .input-row.direct-row .rate { grid-column: 2; }
        .input-row.direct-row .total-field { grid-column: 3; }
        .input-row.single-factor-row { grid-template-columns: minmax(170px, 2fr) minmax(110px, 1fr) minmax(110px, 1fr) minmax(110px, 1fr); }
        .input-row.single-factor-row .rate { grid-column: 2; }
        .input-row.single-factor-row .factor1 { grid-column: 3; }
        .input-row.single-factor-row .total-field { grid-column: 4; }
        .header-row { font-weight: bold; border-bottom: 2px solid #eee; padding-bottom: 5px; font-size: 0.9rem; }
        .header-row > div { text-align: center; }
        .header-row > div:first-child { text-align: left; }
        .formula-note { font-size: 0.85rem; color: #6c757d; margin-bottom: 10px; }
        .subtotal-row { display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #ddd; margin-top: 12px; padding-top: 10px; font-weight: 700; color: var(--accent-dark); }
        .fixed-factor { background: #f8f9fa; color: #6c757d; }
        .summary-box { background: #fff; border: 2px solid var(--accent); border-radius: 12px; padding: 25px; position: sticky; top: 20px; max-height: calc(100vh - 40px); overflow-y: auto; }
        .highlight-blue { background: #e7f1ff; padding: 15px; border-radius: 8px; border-left: 5px solid #0d6efd; margin-top: 15px; }
        .highlight-purple { background: #f3e5f5; padding: 15px; border-radius: 8px; border-left: 5px solid var(--accent); margin-top: 15px; }
        .roi-card { background: #fff; border: 1px solid #ececec; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.06); padding: 16px; margin-top: 20px; }
        .roi-meta { font-size: 0.9rem; color: #6c757d; margin-bottom: 10px; }
        .roi-toggle { width: 100%; border: 0; background: transparent; display: flex; align-items: center; justify-content: space-between; text-align: left; padding: 0; font-size: 1.05rem; font-weight: 600; color: #212529; }
        .roi-toggle .toggle-icon { font-size: 0.85rem; transform: rotate(-90deg); transition: transform 0.2s ease; }
        .roi-card.open .roi-toggle .toggle-icon { transform: rotate(0deg); }
        .roi-body { display: none; margin-top: 10px; }
        .roi-card.open .roi-body { display: block; }
        .roi-chart-wrap { height: 260px; }
        input { width: 100%; padding: 6px 8px; border: 1px solid #ccc; border-radius: 4px; text-align: center; min-height: 36px; }
        .total-field { background: #f8f9fa; font-weight: bold; border: 1px solid #dee2e6; text-align: right; }

        @media (max-width: 992px) {
            .summary-box { position: static; top: auto; max-height: none; overflow-y: visible; }
        }

        @media (max-width: 768px) {
            .input-row { grid-template-columns: 1fr 1fr; gap: 8px; }
            .header-row { display: none; }
            .input-row > span,
            .input-row .rate,
            .input-row .days,
            .input-row .staff,
            .input-row .total-field {
                grid-column: auto;
            }
            .input-row > span { grid-column: 1 / -1; }
            .input-row .total-field { grid-column: 1 / -1; }
        }
    </style>
</head>
<body>

<?php
if (is_super_admin()) {
    require_once __DIR__ . '/partials/header_super.php';
} else {
    require_once __DIR__ . '/partials/header.php';
}
?>

<div class="calculator-container">
    <div class="row">
        <div class="col-lg-8">
            <h2 class="mb-4">Cost Calculator</h2>

            <?php if ($flash): ?>
                <div class="alert alert-info"><?= htmlspecialchars($flash) ?></div>
            <?php endif; ?>
            
            <form id="save_calc_form" method="post" action="/ACE/admin/cost_calculator_save.php" onsubmit="return prepareSavePayload()">
                <?= ace_csrf_input(); ?>
                <input type="hidden" name="record_id" id="record_id" value="<?= (int)($edit_record['id'] ?? 0) ?>">
                <input type="hidden" name="summary_payload" id="summary_payload">
                <input type="hidden" name="calculation_payload" id="calculation_payload">

            <div class="section-card p-3 mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <label>Actual Participants</label>
                        <input type="number" id="actual_pax" value="0" data-default="0" autocomplete="off" oninput="calculateAll()">
                    </div>
                    <div class="col-md-4">
                        <label>Suggested Fee per Pax (RM)</label>
                        <input type="number" id="suggested_fee" value="0" data-default="0" autocomplete="off" oninput="calculateAll()">
                    </div>
                    <div class="col-md-4">
                        <label>Profit Margin %</label>
                        <input type="number" id="profit_margin" value="25" data-default="25" step="0.5" autocomplete="off" oninput="calculateAll()">
                    </div>
                    <div class="col-md-4 mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="use_hrd_corp" checked onchange="calculateAll()">
                            <label class="form-check-label" for="use_hrd_corp">HRD Corp Charges (4%): <strong id="use_hrd_corp_state">ON</strong></label>
                        </div>
                    </div>
                    <div class="col-md-8 mt-3">
                        <label>Calculator Name (before save)</label>
                        <input type="text" name="calc_name" id="calc_name" maxlength="150" placeholder="e.g. IAQ March Intake" autocomplete="off" required value="<?= htmlspecialchars((string)($edit_record['calc_name'] ?? '')) ?>">
                    </div>
                    <div class="col-md-4 mt-3 d-flex gap-2 justify-content-md-end align-items-end">
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="resetAllAndRecalculate()">Reset All</button>
                        <button type="submit" class="btn btn-accent btn-sm">Save</button>
                        <a href="/ACE/admin/cost_calculator_records.php" class="btn btn-outline-secondary btn-sm">Saved Records</a>
                    </div>
                </div>
            </div>
            </form>

            <div id="sections_container"></div>

            <div class="roi-card" id="roi_card">
                <button type="button" class="roi-toggle" id="roi_toggle_btn" onclick="toggleRoiCard()" aria-expanded="false">
                    <span>ROI Graph</span>
                    <span class="toggle-icon">▼</span>
                </button>
                <div class="roi-body" id="roi_body">
                    <div class="roi-meta" id="roi_meta">Break-even marks where all costs are covered. ROI values above 0% indicate profit.</div>
                    <div class="roi-chart-wrap">
                        <canvas id="roiChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="summary-box">
                <h4>Calculation Summary</h4>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span>SUB TOTAL [A]:</span>
                    <strong id="sub_a">RM 0.00</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>SUB TOTAL [B]:</span>
                    <strong id="sub_b">RM 0.00</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>SUB TOTAL [C]:</span>
                    <strong id="sub_c">RM 0.00</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>SUB TOTAL [D]:</span>
                    <strong id="sub_d">RM 0.00</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>SUB TOTAL [E]:</span>
                    <strong id="sub_e">RM 0.00</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span>Expected Total Expenses:</span>
                    <strong id="res_ete">RM 0.00</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Contingency (5%):</span>
                    <span id="res_contingency">RM 0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal After Contingency:</span>
                    <span id="res_scete">RM 0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Other Management Service Charges:</span>
                    <span id="res_mgmt">RM 0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal After Service Charges:</span>
                    <span id="res_smsc">RM 0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Profit Margin:</span>
                    <span id="res_profit">RM 0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal After Profit Margin:</span>
                    <span id="res_spf">RM 0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span id="label_hrd_charge">HRD Corp Charges:</span>
                    <span id="res_hrd">RM 0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span id="label_total_required">Subtotal After HRD Corp Charges:</span>
                    <span id="res_total_required" class="fw-semibold text-primary">RM 0.00</span>
                </div>

                <div class="highlight-blue text-center">
                    <small>MINIMUM FEE REQUIRED</small>
                    <h3 id="out_min_fee">RM 0.00</h3>
                    <small>per participant to cover all costs</small>
                </div>

                <div class="highlight-purple text-center">
                    <small>MINIMUM PARTICIPANTS</small>
                    <h3 id="out_min_pax">0</h3>
                    <small>needed at current suggested fee</small>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
// ROI chart library
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const editSummaryData = <?= json_encode($edit_summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}' ?>;
const editDetailsData = <?= json_encode($edit_details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]' ?>;
const isEditMode = Number(document.getElementById('record_id')?.value || 0) > 0;

const calculatorSections = [
    {
        key: 'A',
        title: 'SECTION A: TRAINER COSTS',
        formula: 'A1-A10: Cost per unit × Hours/Days × No. of staff | A11-A22: Cost per unit × Pax × Days | A23-A24: Cost per unit × Pax × Trip | A25: Rate per km × km × Trip | A26-A27: Charges × Pax × Trip',
        factor1Label: 'Factor 1',
        factor2Label: 'Factor 2',
        items: [
            { code: 'A1', label: 'Speakers', mode: 'x3', rate: 2000, factor1: 1, factor2: 3 },
            { code: 'A2', label: 'Speakers - Industry', mode: 'x3' },
            { code: 'A3', label: 'Facilitator - GMP (2 hour Discussion + 3 hour Presentation)', mode: 'x3' },
            { code: 'A4', label: 'Assistant to trainer + SP', mode: 'x3' },
            { code: 'A5', label: 'Case Writer - OSCE Preparation', mode: 'x3' },
            { code: 'A6', label: 'Station Examiners Review', mode: 'x3' },
            { code: 'A7', label: 'Committee (RM 100 × days)', mode: 'x3' },
            { code: 'A8', label: 'Asst. Facilitator', mode: 'x3' },
            { code: 'A9', label: 'ACE & ILD Staff', mode: 'x3' },
            { code: 'A10', label: 'Others/Staff Overtime', mode: 'x3' },
            { code: 'A11', label: 'Subsistence Allowance for Facilitators', mode: 'x3' },
            { code: 'A12', label: 'Subsistence Allowance for Assistants', mode: 'x3' },
            { code: 'A13', label: 'Subsistence Allowance Committee', mode: 'x3', rate: 30, factor1: 4, factor2: 3 },
            { code: 'A14', label: 'Subsistence Allowance for Others', mode: 'x3' },
            { code: 'A15', label: 'Accommodation for Speakers', mode: 'x3' },
            { code: 'A16', label: 'Accommodation for UniKL Assistant', mode: 'x3' },
            { code: 'A17', label: 'Accommodation for UniKL Committee', mode: 'x3' },
            { code: 'A18', label: 'Accommodation for Others', mode: 'x3' },
            { code: 'A19', label: 'Accommodation for UniKL Facilitators', mode: 'x3' },
            { code: 'A20', label: 'Accommodation for UniKL Assistant', mode: 'x3' },
            { code: 'A21', label: 'Accommodation for UniKL Admin Officers', mode: 'x3' },
            { code: 'A22', label: 'Accommodation for Moderator (External) + rest room for speakers', mode: 'x3' },
            { code: 'A23', label: 'Flights for Speaker', mode: 'x3' },
            { code: 'A24', label: 'Airport Transfer', mode: 'x3' },
            { code: 'A25', label: 'Mileage for Committee', mode: 'x3' },
            { code: 'A26', label: 'Toll', mode: 'x3' },
            { code: 'A27', label: 'Extra Luggage', mode: 'x3' }
        ]
    },
    {
        key: 'B',
        title: 'SECTION B: PROGRAMME COST',
        formula: 'B1-B8: Cost per unit × Pax × Days | B9-B14: Cost per day × Days',
        factor1Label: 'Pax / Days',
        factor2Label: 'Days / Fixed 1',
        items: [
            { code: 'B1', label: 'Accommodation for Participants I', mode: 'x3' },
            { code: 'B2', label: 'Accommodation for Participants II', mode: 'x3' },
            { code: 'B3', label: 'Transportation', mode: 'x3' },
            { code: 'B4', label: 'Training Room - Package with 2 time tea break + lunch', mode: 'x3', rate: 120, factor1: 58, factor2: 1 },
            { code: 'B5', label: 'Training Lab/ Workshop/ Studio', mode: 'x3' },
            { code: 'B6', label: 'Equipment/ Hardware', mode: 'x3' },
            { code: 'B7', label: 'Maintenance Fees', mode: 'x3' },
            { code: 'B8', label: 'Module by Prominent Speaker', mode: 'x3' },
            { code: 'B9', label: 'Utilities', mode: 'x2' },
            { code: 'B10', label: 'Raw Materials/ Others', mode: 'x2' },
            { code: 'B11', label: 'Refreshment - VIP for Opening', mode: 'x2' },
            { code: 'B12', label: 'Dinner', mode: 'x2' },
            { code: 'B13', label: 'Others (Mineral Water)', mode: 'x2' },
            { code: 'B14', label: 'Others (Cameraman, etc.)', mode: 'x2' }
        ]
    },
    {
        key: 'C',
        title: 'SECTION C: TRAINING MATERIALS COST',
        formula: 'C1-C12: Cost per unit × Pax',
        factor1Label: 'Pax',
        factor2Label: 'Fixed 1',
        items: [
            { code: 'C1', label: 'Training Notes', mode: 'x2' },
            { code: 'C2', label: 'Stationeries (File, Pen etc)', mode: 'x2' },
            { code: 'C3', label: 'Book', mode: 'x2' },
            { code: 'C4', label: 'Welcome Pack', mode: 'x2', rate: 30, factor1: 50 },
            { code: 'C5', label: 'Certificate & Certificate Holder', mode: 'x2' },
            { code: 'C6', label: 'CDs/ Photo/ Film etc.', mode: 'x2' },
            { code: 'C7', label: 'Souvenirs for VIP', mode: 'x2' },
            { code: 'C8', label: 'Souveniers to Speaker', mode: 'x2' },
            { code: 'C9', label: 'Bunting', mode: 'x2' },
            { code: 'C10', label: 'Insurance', mode: 'x2' },
            { code: 'C11', label: 'Consumables for OSCE stations', mode: 'x2' },
            { code: 'C12', label: 'Training Manual & Notes Development Fees', mode: 'x2' }
        ]
    },
    {
        key: 'D',
        title: 'SECTION D: MARKETING & PROMOTION COST',
        formula: 'D1-D13: Cost per unit × Unit × Days',
        factor1Label: 'Unit',
        factor2Label: 'Days',
        items: [
            { code: 'D1', label: 'Advertisement/ Newspaper', mode: 'x3' },
            { code: 'D2', label: 'Brochures/ Flyers/ Letters', mode: 'x3' },
            { code: 'D3', label: 'Posters/ Bunting', mode: 'x3' },
            { code: 'D4', label: 'Telemarketing/ Phone calls/ Faxing', mode: 'x3' },
            { code: 'D5', label: 'Exhibition/ Booth', mode: 'x3' },
            { code: 'D6', label: 'Postage', mode: 'x3' },
            { code: 'D7', label: 'Sponsorship', mode: 'x3' },
            { code: 'D8', label: 'Professional Services', mode: 'x3' },
            { code: 'D9', label: 'Graphic Artist', mode: 'x3' },
            { code: 'D10', label: 'Language Expert', mode: 'x3' },
            { code: 'D11', label: 'Travelling & Allowances', mode: 'x3' },
            { code: 'D12', label: 'Part-Timers', mode: 'x3' },
            { code: 'D13', label: 'Others', mode: 'x3' }
        ]
    },
    {
        key: 'E',
        title: 'SECTION E: COMMISSION COST',
        formula: 'E1-E3: Cost per unit × Pax × Days',
        factor1Label: 'Pax',
        factor2Label: 'Days',
        items: [
            { code: 'E1', label: 'Referrer (Estimated)', mode: 'x3' },
            { code: 'E2', label: 'Agent', mode: 'x3' },
            { code: 'E3', label: 'Miscellaneous', mode: 'x3' }
        ]
    }
];

function formatRM(value) {
    return 'RM ' + (Number(value) || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

let latestSummary = null;
let latestDetails = [];
let roiChart = null;
const editDetailsByCode = Array.isArray(editDetailsData)
    ? editDetailsData.reduce((acc, row) => {
        if (row && row.code) {
            acc[String(row.code).trim()] = row;
        }
        return acc;
    }, {})
    : {};

function getPrefillValue(code, fieldName, fallback = 0) {
    if (!isEditMode) return Number(fallback) || 0;

    const row = editDetailsByCode[String(code || '').trim()];
    if (!row) return Number(fallback) || 0;

    const candidate = Number(row[fieldName]);
    if (!Number.isFinite(candidate)) return Number(fallback) || 0;
    return candidate;
}

function makeInput(value, className, editable = true, code = '') {
    const safeValue = Number(value) || 0;
    if (!editable) {
        return `<input type="number" class="${className} fixed-factor" value="1" readonly tabindex="-1">`;
    }
    const inputValue = getPrefillValue(code, className, isEditMode ? safeValue : 0);
    return `<input type="number" class="${className}" value="${inputValue}" data-default="0" autocomplete="off" step="0.01" oninput="calculateAll()">`;
}

function renderSections() {
    const container = document.getElementById('sections_container');
    container.innerHTML = calculatorSections.map(section => {
        const isDirectSection = section.key === 'A' || section.key === 'E';
        const isSingleFactorSection = section.key === 'C';
        const effectiveFormula = isDirectSection ? 'Enter total amount only (no multiplication required).' : section.formula;

        const rows = section.items.map(item => `
            <div class="input-row calc-row ${isDirectSection ? 'direct-row' : ''} ${isSingleFactorSection ? 'single-factor-row' : ''}" data-section="${section.key}" data-mode="${isDirectSection ? 'direct' : item.mode}">
                <span><strong>${item.code}</strong> ${item.label}</span>
                ${makeInput(item.rate, 'rate', true, item.code)}
                ${isDirectSection ? '' : makeInput(item.factor1, 'factor1', true, item.code)}
                ${isDirectSection || isSingleFactorSection ? '' : makeInput(item.factor2, 'factor2', item.mode === 'x3', item.code)}
                <input type="text" class="total-field" readonly>
            </div>
        `).join('');

        const headerRow = isDirectSection
            ? `
                <div class="input-row header-row direct-row">
                    <div>Description</div>
                    <div>Amount (RM)</div>
                    <div>Total (RM)</div>
                </div>
            `
            : isSingleFactorSection
            ? `
                <div class="input-row header-row single-factor-row">
                    <div>Description</div>
                    <div>Cost/Rate (RM)</div>
                    <div>${section.factor1Label}</div>
                    <div>Total (RM)</div>
                </div>
            `
            : `
                <div class="input-row header-row">
                    <div>Description</div>
                    <div>Cost/Rate (RM)</div>
                    <div>${section.factor1Label}</div>
                    <div>${section.factor2Label}</div>
                    <div>Total (RM)</div>
                </div>
            `;

        return `
            <div class="section-card collapsible ${section.key === 'A' ? 'open' : ''}">
                <div class="section-header" onclick="toggleSection(this)">
                    <span>${section.title}</span>
                    <span class="toggle-icon">▼</span>
                </div>
                <div class="section-body">
                    <div class="formula-note">${effectiveFormula}</div>
                    ${headerRow}
                    ${rows}
                    <div class="subtotal-row">
                        <span>SUB TOTAL [${section.key}]</span>
                        <span id="section_sub_${section.key}">RM 0.00</span>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function toggleSection(headerEl) {
    const card = headerEl.closest('.section-card');
    if (!card) return;
    const isAlreadyOpen = card.classList.contains('open');
    document.querySelectorAll('#sections_container .section-card.collapsible.open').forEach(openCard => {
        openCard.classList.remove('open');
    });
    if (!isAlreadyOpen) {
        card.classList.add('open');
    }
}

function resetAllAndRecalculate() {
    document.querySelectorAll('#sections_container input[type="number"]').forEach(input => {
        if (input.readOnly || input.classList.contains('fixed-factor')) return;
        input.value = '0';
    });

    const paxEl = document.getElementById('actual_pax');
    const feeEl = document.getElementById('suggested_fee');
    const profitEl = document.getElementById('profit_margin');

    if (paxEl) paxEl.value = '0';
    if (feeEl) feeEl.value = '0';
    if (profitEl) profitEl.value = profitEl.dataset.default ?? '25';
    const useHrdEl = document.getElementById('use_hrd_corp');
    if (useHrdEl) useHrdEl.checked = true;

    const calcNameEl = document.getElementById('calc_name');
    if (calcNameEl) calcNameEl.value = '';
    const recordIdEl = document.getElementById('record_id');
    if (recordIdEl) recordIdEl.value = '0';

    calculateAll();
}

function prepareSavePayload() {
    const calcNameEl = document.getElementById('calc_name');
    if (!calcNameEl || !calcNameEl.value.trim()) {
        alert('Please enter a calculator name before saving.');
        return false;
    }

    if (!latestSummary || !Array.isArray(latestDetails)) {
        calculateAll();
    }

    document.getElementById('summary_payload').value = JSON.stringify(latestSummary || {});
    document.getElementById('calculation_payload').value = JSON.stringify(latestDetails || []);
    return true;
}

function updateRoiChart(totalRequired, participants, suggestedFee) {
    const canvas = document.getElementById('roiChart');
    if (!canvas || typeof Chart === 'undefined') return;

    const safeRequired = Number(totalRequired) || 0;
    const safeParticipants = Math.max(0, Number(participants) || 0);
    const safeFee = Math.max(0, Number(suggestedFee) || 0);

    const maxParticipants = Math.max(10, Math.ceil(safeParticipants * 2), 50);
    const labels = [];
    const roiData = [];
    const lossData = [];
    const profitData = [];
    const zeroLineData = [];

    for (let pax = 1; pax <= maxParticipants; pax += 1) {
        const revenue = pax * safeFee;
        const roi = safeRequired > 0 ? ((revenue - safeRequired) / safeRequired) * 100 : 0;
        const roundedRoi = Number.isFinite(roi) ? Number(roi.toFixed(2)) : 0;
        labels.push(pax);
        roiData.push(roundedRoi);
        lossData.push(roundedRoi < 0 ? roundedRoi : null);
        profitData.push(roundedRoi >= 0 ? roundedRoi : null);
        zeroLineData.push(0);
    }

    const breakEvenPax = safeFee > 0 ? Math.ceil(safeRequired / safeFee) : 0;
    const currentRevenue = safeParticipants * safeFee;
    const currentRoi = safeRequired > 0 ? ((currentRevenue - safeRequired) / safeRequired) * 100 : 0;
    const currentRoiRounded = Number.isFinite(currentRoi) ? Number(currentRoi.toFixed(2)) : 0;

    const breakEvenPointData = new Array(labels.length).fill(null);
    const currentPointData = new Array(labels.length).fill(null);
    if (breakEvenPax >= 1 && breakEvenPax <= labels.length) {
        breakEvenPointData[breakEvenPax - 1] = 0;
    }
    if (safeParticipants >= 1 && safeParticipants <= labels.length) {
        currentPointData[safeParticipants - 1] = currentRoiRounded;
    }

    const roiMeta = document.getElementById('roi_meta');
    if (roiMeta) {
        roiMeta.innerText = `Current ROI: ${currentRoi.toFixed(2)}% • Break-even participants: ${breakEvenPax} • Red zone = below cost, Green zone = profit`;
    }

    if (!roiChart) {
        roiChart = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'ROI % (Trend)',
                        data: roiData,
                        borderColor: '#6f42c1',
                        backgroundColor: 'rgba(111,66,193,0.08)',
                        tension: 0.25,
                        fill: false,
                        pointRadius: 0
                    },
                    {
                        label: 'Below Cost (Loss Zone)',
                        data: lossData,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220,53,69,0.18)',
                        tension: 0.25,
                        fill: 'origin',
                        pointRadius: 0
                    },
                    {
                        label: 'Profit Zone',
                        data: profitData,
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25,135,84,0.18)',
                        tension: 0.25,
                        fill: 'origin',
                        pointRadius: 0
                    },
                    {
                        label: 'Break-even (ROI 0%)',
                        data: zeroLineData,
                        borderColor: '#6c757d',
                        borderDash: [6, 4],
                        pointRadius: 0,
                        tension: 0,
                        fill: false
                    },
                    {
                        label: `Break-even @ ${breakEvenPax} pax`,
                        data: breakEvenPointData,
                        borderColor: '#fd7e14',
                        backgroundColor: '#fd7e14',
                        pointRadius: 5,
                        pointHoverRadius: 6,
                        showLine: false
                    },
                    {
                        label: `Current @ ${safeParticipants} pax`,
                        data: currentPointData,
                        borderColor: '#0d6efd',
                        backgroundColor: '#0d6efd',
                        pointRadius: 5,
                        pointHoverRadius: 6,
                        showLine: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: { display: true, text: 'Number of Participants' }
                    },
                    y: {
                        title: { display: true, text: 'ROI (%)' }
                    }
                },
                plugins: {
                    legend: { display: true, position: 'bottom' }
                }
            }
        });
    } else {
        roiChart.data.labels = labels;
        roiChart.data.datasets[0].data = roiData;
        roiChart.data.datasets[1].data = lossData;
        roiChart.data.datasets[2].data = profitData;
        roiChart.data.datasets[3].data = zeroLineData;
        roiChart.data.datasets[4].label = `Break-even @ ${breakEvenPax} pax`;
        roiChart.data.datasets[4].data = breakEvenPointData;
        roiChart.data.datasets[5].label = `Current @ ${safeParticipants} pax`;
        roiChart.data.datasets[5].data = currentPointData;
        roiChart.update('none');
    }
}

function toggleRoiCard() {
    const card = document.getElementById('roi_card');
    const btn = document.getElementById('roi_toggle_btn');
    if (!card || !btn) return;

    const willOpen = !card.classList.contains('open');
    card.classList.toggle('open', willOpen);
    btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

    if (willOpen && roiChart) {
        roiChart.resize();
        roiChart.update('none');
    }
}

function calculateAll() {
    const subtotals = { A: 0, B: 0, C: 0, D: 0, E: 0 };
    latestDetails = [];

    document.querySelectorAll('.calc-row').forEach(row => {
        const sectionKey = row.dataset.section;
        const mode = row.dataset.mode;
        const isDirectAmount = sectionKey === 'A' || sectionKey === 'E' || mode === 'direct';
        const codeText = row.querySelector('span strong')?.innerText || '';
        const labelText = (row.querySelector('span')?.innerText || '').replace(codeText, '').trim();
        const rate = parseFloat(row.querySelector('.rate')?.value) || 0;
        const factor1 = isDirectAmount ? 1 : (parseFloat(row.querySelector('.factor1')?.value) || 0);
        const factor2 = isDirectAmount ? 1 : (mode === 'x3' ? (parseFloat(row.querySelector('.factor2')?.value) || 0) : 1);
        const rowTotal = isDirectAmount ? rate : (rate * factor1 * factor2);

        row.querySelector('.total-field').value = (Number(rowTotal) || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        subtotals[sectionKey] += rowTotal;
        latestDetails.push({
            section: sectionKey,
            code: codeText,
            label: labelText,
            mode: mode,
            rate: rate,
            factor1: factor1,
            factor2: factor2,
            row_total: rowTotal
        });
    });

    const ete = subtotals.A + subtotals.B + subtotals.C + subtotals.D + subtotals.E;

    let profitMargin = parseFloat(document.getElementById('profit_margin').value);
    if (Number.isNaN(profitMargin)) profitMargin = 25;
    document.getElementById('profit_margin').value = profitMargin;
    const profitRate = profitMargin / 100;

    const actualPax = Math.max(0, parseFloat(document.getElementById('actual_pax').value) || 0);
    const suggestedFee = Math.max(0, parseFloat(document.getElementById('suggested_fee').value) || 0);

    const total = actualPax * suggestedFee;
    const contingencyRate = 0.05;
    const managementRate = 0.10;
    const hrdRate = 0.04;
    const useHrd = !!document.getElementById('use_hrd_corp')?.checked;
    const effectiveHrdRate = useHrd ? hrdRate : 0;

    const cete = contingencyRate * ete;
    const scete = ete + cete;
    const hrdCorpCharges = effectiveHrdRate * total;
    const denominator = 1 - managementRate - profitRate;
    const subtotalAfterProfit = denominator > 0 ? (scete / denominator) : 0;
    const subtotalAfterHrd = (1 - managementRate - profitRate - effectiveHrdRate) > 0 ? (scete / (1 - managementRate - profitRate - effectiveHrdRate)) : 0;

    const otherServiceCharges = managementRate * subtotalAfterHrd;
    const subtotalAfterService = scete + otherServiceCharges;
    const profitAmount = profitRate * subtotalAfterHrd;

    const minFee = actualPax > 0 ? (subtotalAfterHrd / actualPax) : 0;
    const minPax = suggestedFee > 0 ? Math.ceil(scete / suggestedFee) : 0;

    document.getElementById('section_sub_A').innerText = formatRM(subtotals.A);
    document.getElementById('section_sub_B').innerText = formatRM(subtotals.B);
    document.getElementById('section_sub_C').innerText = formatRM(subtotals.C);
    document.getElementById('section_sub_D').innerText = formatRM(subtotals.D);
    document.getElementById('section_sub_E').innerText = formatRM(subtotals.E);

    document.getElementById('sub_a').innerText = formatRM(subtotals.A);
    document.getElementById('sub_b').innerText = formatRM(subtotals.B);
    document.getElementById('sub_c').innerText = formatRM(subtotals.C);
    document.getElementById('sub_d').innerText = formatRM(subtotals.D);
    document.getElementById('sub_e').innerText = formatRM(subtotals.E);

    document.getElementById('res_ete').innerText = formatRM(ete);
    document.getElementById('res_contingency').innerText = formatRM(cete);
    document.getElementById('res_scete').innerText = formatRM(scete);
    document.getElementById('res_mgmt').innerText = formatRM(otherServiceCharges);
    document.getElementById('res_smsc').innerText = formatRM(subtotalAfterService);
    document.getElementById('res_profit').innerText = formatRM(profitAmount);
    document.getElementById('res_spf').innerText = formatRM(subtotalAfterProfit);
    document.getElementById('res_hrd').innerText = formatRM(hrdCorpCharges);
    document.getElementById('res_total_required').innerText = formatRM(subtotalAfterHrd);

    const hrdLabelEl = document.getElementById('label_hrd_charge');
    const totalRequiredLabelEl = document.getElementById('label_total_required');
    const hrdStateEl = document.getElementById('use_hrd_corp_state');
    if (hrdLabelEl) {
        hrdLabelEl.innerText = useHrd ? 'HRD Corp Charges:' : 'HRD Corp Charges (Not Applied):';
    }
    if (totalRequiredLabelEl) {
        totalRequiredLabelEl.innerText = useHrd ? 'Subtotal After HRD Corp Charges:' : 'Subtotal After Profit Margin:';
    }
    if (hrdStateEl) {
        hrdStateEl.innerText = useHrd ? 'ON' : 'OFF';
    }

    document.getElementById('out_min_fee').innerText = formatRM(minFee);
    document.getElementById('out_min_pax').innerText = Number.isFinite(minPax) ? minPax : 0;

    updateRoiChart(scete, actualPax, suggestedFee);

    latestSummary = {
        participants: actualPax,
        suggested_fee: suggestedFee,
        profit_margin: profitMargin,
        subtotals: subtotals,
        expected_total_expenses: ete,
        contingency: cete,
        subtotal_after_contingency: scete,
        management_service_charges: otherServiceCharges,
        subtotal_after_service_charges: subtotalAfterService,
        profit_amount: profitAmount,
        subtotal_after_profit_margin: subtotalAfterProfit,
        hrd_corp_charges: hrdCorpCharges,
        subtotal_after_hrd_charges: subtotalAfterHrd,
        use_hrd_corp: useHrd,
        minimum_fee_per_participant: minFee,
        minimum_participants_to_cover_cost: Number.isFinite(minPax) ? minPax : 0
    };
}

window.onload = function () {
    renderSections();
    if (isEditMode) {
        const participants = Number(editSummaryData.participants ?? 0);
        const suggestedFee = Number(editSummaryData.suggested_fee ?? 0);
        const profitMargin = Number(editSummaryData.profit_margin ?? 25);
        const useHrd = typeof editSummaryData.use_hrd_corp === 'boolean'
            ? editSummaryData.use_hrd_corp
            : Number(editSummaryData.hrd_corp_charges ?? 0) > 0;

        const paxEl = document.getElementById('actual_pax');
        const feeEl = document.getElementById('suggested_fee');
        const profitEl = document.getElementById('profit_margin');
        const useHrdEl = document.getElementById('use_hrd_corp');

        if (paxEl) paxEl.value = Number.isFinite(participants) ? participants : 0;
        if (feeEl) feeEl.value = Number.isFinite(suggestedFee) ? suggestedFee : 0;
        if (profitEl) profitEl.value = Number.isFinite(profitMargin) ? profitMargin : 25;
        if (useHrdEl) useHrdEl.checked = !!useHrd;

        calculateAll();
        return;
    }

    resetAllAndRecalculate();
};

window.addEventListener('pageshow', function () {
    if (!isEditMode) {
        resetAllAndRecalculate();
    }
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

</body>
</html>