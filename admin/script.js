const sectionTotals = {
    A: 0,
    B: 0,
    C: 0,
    D: 0,
    E: 0
};

const sectionASubtotals = {
    staff: 0,
    subsistence: 0,
    accommodation: 0,
    transportation: 0,
    mileageOther: 0
};

const sectionBSubtotals = {
    accommodation: 0,
    transportation: 0,
    trainingFacilities: 0,
    equipmentMaintenance: 0,
    materialsRefreshments: 0
};

const sectionCSubtotals = {
    trainingMaterials: 0,
    stationerySupplies: 0,
    certificatesSouvenirs: 0,
    insuranceConsumables: 0,
    developmentFees: 0
};

const sectionDSubtotals = {
    advertisingMedia: 0,
    printMaterials: 0,
    professionalServices: 0,
    staffTravel: 0,
    others: 0
};

const sectionESubtotals = {
    referrer: 0,
    agent: 0,
    miscellaneous: 0
};

function getNumber(id) {
    const el = document.getElementById(id);
    return el ? parseFloat(el.value) || 0 : 0;
}

function setMoney(id, value) {
    const el = document.getElementById(id);
    if (el) {
        el.textContent = 'RM ' + value.toFixed(2);
    }
}

function setMoneyAll(id, value) {
    document.querySelectorAll('[id="' + id + '"]').forEach(el => {
        el.textContent = 'RM ' + value.toFixed(2);
    });
}

function toggleSection(sectionId) {
    const content = document.getElementById(sectionId + '-content');
    const icon = document.getElementById(sectionId + '-icon');

    if (!content || !icon) {
        return;
    }

    if (content.classList.contains('open')) {
        content.classList.remove('open');
        icon.classList.remove('rotated');
    } else {
        content.classList.add('open');
        icon.classList.add('rotated');
    }
}

function calculateSectionA() {
    sectionASubtotals.staff =
        (getNumber('speakers_rate') * getNumber('speakers_days') * getNumber('speakers_staff')) +
        (getNumber('speakers_industry_rate') * getNumber('speakers_industry_days') * getNumber('speakers_industry_staff')) +
        (getNumber('facilitator_rate') * getNumber('facilitator_days') * getNumber('facilitator_staff')) +
        (getNumber('case_writer_rate') * getNumber('case_writer_days') * getNumber('case_writer_staff')) +
        (getNumber('station_examiners_rate') * getNumber('station_examiners_days') * getNumber('station_examiners_staff')) +
        (getNumber('committee_rate') * getNumber('committee_days') * getNumber('committee_staff')) +
        (getNumber('assist_rate') * getNumber('assist_days') * getNumber('assist_staff')) +
        (getNumber('ace_ild_staff_rate') * getNumber('ace_ild_staff_days') * getNumber('ace_ild_staff_staff')) +
        (getNumber('others_staff_overtime_rate') * getNumber('others_staff_overtime_hours') * getNumber('others_staff_overtime_staff'));

    sectionASubtotals.subsistence =
        (getNumber('subsistence_facilitators_rate') * getNumber('subsistence_facilitators_days') * getNumber('subsistence_facilitators_staff')) +
        (getNumber('subsistence_assistants_rate') * getNumber('subsistence_assistants_days') * getNumber('subsistence_assistants_staff')) +
        (getNumber('subsistence_committee_rate') * getNumber('subsistence_committee_days') * getNumber('subsistence_committee_staff')) +
        (getNumber('subsistence_others_rate') * getNumber('subsistence_others_days') * getNumber('subsistence_others_staff'));

    sectionASubtotals.accommodation =
        (getNumber('accommodation_speakers_rate') * getNumber('accommodation_speakers_days') * getNumber('accommodation_speakers_staff')) +
        (getNumber('accommodation_unikl_assistant_rate') * getNumber('accommodation_unikl_assistant_days') * getNumber('accommodation_unikl_assistant_staff')) +
        (getNumber('accommodation_unikl_committee_rate') * getNumber('accommodation_unikl_committee_days') * getNumber('accommodation_unikl_committee_staff')) +
        (getNumber('accommodation_others_rate') * getNumber('accommodation_others_days') * getNumber('accommodation_others_staff')) +
        (getNumber('accommodation_unikl_facilitators_rate') * getNumber('accommodation_unikl_facilitators_days') * getNumber('accommodation_unikl_facilitators_staff')) +
        (getNumber('accommodation_unikl_admin_rate') * getNumber('accommodation_unikl_admin_days') * getNumber('accommodation_unikl_admin_staff')) +
        (getNumber('accommodation_moderator_rate') * getNumber('accommodation_moderator_days') * getNumber('accommodation_moderator_staff'));

    sectionASubtotals.transportation =
        (getNumber('flights_speaker_rate') * getNumber('flights_speaker_pax') * getNumber('flights_speaker_days')) +
        (getNumber('airport_transfer_rate') * getNumber('airport_transfer_pax') * getNumber('airport_transfer_days'));

    sectionASubtotals.mileageOther =
        (getNumber('mileage_committee_rate') * getNumber('mileage_committee_km') * getNumber('mileage_committee_trips')) +
        (getNumber('toll_cost_rate') * getNumber('toll_cost_pax') * getNumber('toll_cost_trips')) +
        (getNumber('luggage_cost_rate') * getNumber('luggage_cost_pax') * getNumber('luggage_cost_trips'));

    sectionTotals.A =
        sectionASubtotals.staff +
        sectionASubtotals.subsistence +
        sectionASubtotals.accommodation +
        sectionASubtotals.transportation +
        sectionASubtotals.mileageOther;

    setMoney('staffCostsTotal', sectionASubtotals.staff);
    setMoney('subsistenceTotal', sectionASubtotals.subsistence);
    setMoney('accommodationTotal', sectionASubtotals.accommodation);
    setMoney('transportationTotal', sectionASubtotals.transportation);
    setMoney('mileageOtherTotal', sectionASubtotals.mileageOther);
    setMoney('totalSectionACost', sectionTotals.A);
}

function calculateSectionB() {
    sectionBSubtotals.accommodation =
        (getNumber('accommodation_participants1_rate') * getNumber('accommodation_participants1_pax') * getNumber('accommodation_participants1_days')) +
        (getNumber('accommodation_participants2_rate') * getNumber('accommodation_participants2_pax') * getNumber('accommodation_participants2_days'));

    sectionBSubtotals.transportation =
        (getNumber('transportation_participants_rate') * getNumber('transportation_participants_pax') * getNumber('transportation_participants_days'));

    sectionBSubtotals.trainingFacilities =
        (getNumber('training_room_rate') * getNumber('training_room_pax') * getNumber('training_room_days')) +
        (getNumber('training_lab_rate') * getNumber('training_lab_pax') * getNumber('training_lab_days'));

    sectionBSubtotals.equipmentMaintenance =
        (getNumber('equipment_hardware_rate') * getNumber('equipment_hardware_pax') * getNumber('equipment_hardware_days')) +
        (getNumber('maintenance_fees_rate') * getNumber('maintenance_fees_pax') * getNumber('maintenance_fees_days'));

    sectionBSubtotals.materialsRefreshments =
        (getNumber('module_prominent_speaker_rate') * getNumber('module_prominent_speaker_pax') * getNumber('module_prominent_speaker_days')) +
        (getNumber('utilities_rate') * getNumber('utilities_pax') * getNumber('utilities_days')) +
        (getNumber('raw_materials_rate') * getNumber('raw_materials_pax') * getNumber('raw_materials_days')) +
        (getNumber('refreshment_vip_rate') * getNumber('refreshment_vip_pax') * getNumber('refreshment_vip_days')) +
        (getNumber('dinner_rate') * getNumber('dinner_pax') * getNumber('dinner_days')) +
        (getNumber('others_mineral_water_rate') * getNumber('others_mineral_water_pax') * getNumber('others_mineral_water_days')) +
        (getNumber('others_cameraman_rate') * getNumber('others_cameraman_pax') * getNumber('others_cameraman_days'));

    sectionTotals.B =
        sectionBSubtotals.accommodation +
        sectionBSubtotals.transportation +
        sectionBSubtotals.trainingFacilities +
        sectionBSubtotals.equipmentMaintenance +
        sectionBSubtotals.materialsRefreshments;

    setMoney('participantAccommodationTotal', sectionBSubtotals.accommodation);
    setMoney('participantTransportationTotal', sectionBSubtotals.transportation);
    setMoney('trainingFacilitiesTotal', sectionBSubtotals.trainingFacilities);
    setMoney('equipmentMaintenanceTotal', sectionBSubtotals.equipmentMaintenance);
    setMoney('materialsRefreshmentsTotal', sectionBSubtotals.materialsRefreshments);
    setMoney('totalSectionBCost', sectionTotals.B);
}

function calculateSectionC() {
    sectionCSubtotals.trainingMaterials =
        (getNumber('training_notes_rate') * getNumber('training_notes_pax')) +
        (getNumber('book_rate') * getNumber('book_pax'));

    sectionCSubtotals.stationerySupplies =
        (getNumber('stationeries_rate') * getNumber('stationeries_pax')) +
        (getNumber('welcome_pack_rate') * getNumber('welcome_pack_pax')) +
        (getNumber('cds_photo_film_rate') * getNumber('cds_photo_film_pax')) +
        (getNumber('bunting_rate') * getNumber('bunting_pax'));

    sectionCSubtotals.certificatesSouvenirs =
        (getNumber('certificate_holder_rate') * getNumber('certificate_holder_pax')) +
        (getNumber('souvenirs_vip_rate') * getNumber('souvenirs_vip_pax')) +
        (getNumber('souvenirs_speaker_rate') * getNumber('souvenirs_speaker_pax'));

    sectionCSubtotals.insuranceConsumables =
        (getNumber('insurance_rate') * getNumber('insurance_pax')) +
        (getNumber('consumables_osce_rate') * getNumber('consumables_osce_pax'));

    sectionCSubtotals.developmentFees =
        (getNumber('training_manual_development_rate') * getNumber('training_manual_development_pax'));

    sectionTotals.C =
        sectionCSubtotals.trainingMaterials +
        sectionCSubtotals.stationerySupplies +
        sectionCSubtotals.certificatesSouvenirs +
        sectionCSubtotals.insuranceConsumables +
        sectionCSubtotals.developmentFees;

    setMoney('trainingMaterialsTotal', sectionCSubtotals.trainingMaterials);
    setMoney('stationerySuppliesTotal', sectionCSubtotals.stationerySupplies);
    setMoney('certificatesSouvenirsTotal', sectionCSubtotals.certificatesSouvenirs);
    setMoney('insuranceConsumablesTotal', sectionCSubtotals.insuranceConsumables);
    setMoney('developmentFeesTotal', sectionCSubtotals.developmentFees);
    setMoney('totalSectionCCost', sectionTotals.C);
}

function calculateSectionD() {
    sectionDSubtotals.advertisingMedia =
        (getNumber('advertisement_newspaper_rate') * getNumber('advertisement_newspaper_unit') * getNumber('advertisement_newspaper_days')) +
        (getNumber('telemarketing_phone_fax_rate') * getNumber('telemarketing_phone_fax_unit') * getNumber('telemarketing_phone_fax_days')) +
        (getNumber('exhibition_booth_rate') * getNumber('exhibition_booth_unit') * getNumber('exhibition_booth_days'));

    sectionDSubtotals.printMaterials =
        (getNumber('brochures_flyers_letters_rate') * getNumber('brochures_flyers_letters_unit') * getNumber('brochures_flyers_letters_days')) +
        (getNumber('posters_bunting_rate') * getNumber('posters_bunting_unit') * getNumber('posters_bunting_days')) +
        (getNumber('postage_rate') * getNumber('postage_unit') * getNumber('postage_days'));

    sectionDSubtotals.professionalServices =
        (getNumber('professional_services_rate') * getNumber('professional_services_unit') * getNumber('professional_services_days')) +
        (getNumber('graphic_artist_rate') * getNumber('graphic_artist_unit') * getNumber('graphic_artist_days')) +
        (getNumber('language_expert_rate') * getNumber('language_expert_unit') * getNumber('language_expert_days'));

    sectionDSubtotals.staffTravel =
        (getNumber('travelling_allowances_rate') * getNumber('travelling_allowances_unit') * getNumber('travelling_allowances_days')) +
        (getNumber('part_timers_rate') * getNumber('part_timers_unit') * getNumber('part_timers_days'));

    sectionDSubtotals.others =
        (getNumber('sponsorship_rate') * getNumber('sponsorship_unit') * getNumber('sponsorship_days')) +
        (getNumber('others_section_d_rate') * getNumber('others_section_d_unit') * getNumber('others_section_d_days'));

    sectionTotals.D =
        sectionDSubtotals.advertisingMedia +
        sectionDSubtotals.printMaterials +
        sectionDSubtotals.professionalServices +
        sectionDSubtotals.staffTravel +
        sectionDSubtotals.others;

    setMoney('advertisingMediaTotal', sectionDSubtotals.advertisingMedia);
    setMoney('printMaterialsTotal', sectionDSubtotals.printMaterials);
    setMoney('professionalServicesTotal', sectionDSubtotals.professionalServices);
    setMoney('staffTravelTotal', sectionDSubtotals.staffTravel);
    setMoney('othersSectionDTotal', sectionDSubtotals.others);
    setMoney('totalSectionDCost', sectionTotals.D);
}

function calculateSectionE() {
    sectionESubtotals.referrer =
        (getNumber('referrer_estimated_rate') * getNumber('referrer_estimated_pax') * getNumber('referrer_estimated_days'));

    sectionESubtotals.agent =
        (getNumber('agent_rate') * getNumber('agent_pax') * getNumber('agent_days'));

    sectionESubtotals.miscellaneous =
        (getNumber('miscellaneous_rate') * getNumber('miscellaneous_pax') * getNumber('miscellaneous_days'));

    sectionTotals.E =
        sectionESubtotals.referrer +
        sectionESubtotals.agent +
        sectionESubtotals.miscellaneous;

    setMoney('referrerEstimatedTotal', sectionESubtotals.referrer);
    setMoney('agentTotal', sectionESubtotals.agent);
    setMoney('miscellaneousTotal', sectionESubtotals.miscellaneous);
    setMoney('totalSectionECost', sectionTotals.E);
}

function updateChart() {
    const maxValue = Math.max(
        sectionASubtotals.staff,
        sectionASubtotals.subsistence,
        sectionASubtotals.accommodation,
        sectionASubtotals.transportation,
        sectionASubtotals.mileageOther
    );

    const safeMax = maxValue > 0 ? maxValue : 1;

    const setBar = (barId, labelId, value) => {
        const bar = document.getElementById(barId);
        const label = document.getElementById(labelId);
        if (bar) {
            bar.style.width = ((value / safeMax) * 100) + '%';
        }
        if (label) {
            label.textContent = 'RM ' + value.toFixed(2);
        }
    };

    setBar('staffCostsBar', 'staffCostsLabel', sectionASubtotals.staff);
    setBar('subsistenceBar', 'subsistenceLabel', sectionASubtotals.subsistence);
    setBar('accommodationBar', 'accommodationLabel', sectionASubtotals.accommodation);
    setBar('transportationBar', 'transportationLabel', sectionASubtotals.transportation);
    setBar('mileageOtherBar', 'mileageOtherLabel', sectionASubtotals.mileageOther);
}

function updateDetailedBreakdown(totalProjectCost) {
    const tbody = document.getElementById('detailedBreakdown');
    if (!tbody) {
        return;
    }

    const rows = [
        { label: 'Section A - Trainer Cost', amount: sectionTotals.A },
        { label: 'Section B - Participant Cost', amount: sectionTotals.B },
        { label: 'Section C - Materials & Supplies', amount: sectionTotals.C },
        { label: 'Section D - Marketing & Promotion', amount: sectionTotals.D },
        { label: 'Section E - Referral & Agent', amount: sectionTotals.E }
    ];

    tbody.innerHTML = '';

    rows.forEach(row => {
        const percentage = totalProjectCost > 0 ? (row.amount / totalProjectCost) * 100 : 0;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.label}</td>
            <td>${'RM ' + row.amount.toFixed(2)}</td>
            <td>${percentage.toFixed(1)}%</td>
        `;
        tbody.appendChild(tr);
    });
}

function updateOverallTotals() {
    const totalProjectCost =
        sectionTotals.A +
        sectionTotals.B +
        sectionTotals.C +
        sectionTotals.D +
        sectionTotals.E;

    setMoneyAll('totalProjectCost', totalProjectCost);
    setMoney('overallSectionB', sectionTotals.B);
    setMoney('overallSectionC', sectionTotals.C);
    setMoney('overallSectionD', sectionTotals.D);
    setMoney('overallSectionE', sectionTotals.E);

    updateChart();
    updateDetailedBreakdown(totalProjectCost);

    const results = document.getElementById('results');
    const noResults = document.getElementById('noResults');

    if (results && noResults) {
        if (totalProjectCost > 0) {
            results.style.display = 'block';
            noResults.style.display = 'none';
        } else {
            results.style.display = 'none';
            noResults.style.display = 'block';
        }
    }
}

document.getElementById('costCalculator')?.addEventListener('submit', (e) => {
    e.preventDefault();
    calculateSectionA();
    updateOverallTotals();
});

document.getElementById('sectionBCalculator')?.addEventListener('submit', (e) => {
    e.preventDefault();
    calculateSectionB();
    updateOverallTotals();
});

document.getElementById('sectionCCalculator')?.addEventListener('submit', (e) => {
    e.preventDefault();
    calculateSectionC();
    updateOverallTotals();
});

document.getElementById('sectionDCalculator')?.addEventListener('submit', (e) => {
    e.preventDefault();
    calculateSectionD();
    updateOverallTotals();
});

document.getElementById('sectionECalculator')?.addEventListener('submit', (e) => {
    e.preventDefault();
    calculateSectionE();
    updateOverallTotals();
});

document.getElementById('calculateAllButton')?.addEventListener('click', () => {
    calculateSectionA();
    calculateSectionB();
    calculateSectionC();
    calculateSectionD();
    calculateSectionE();
    updateOverallTotals();
    const results = document.getElementById('results');
    if (results) {
        results.scrollIntoView({ behavior: 'smooth' });
    }
});

window.addEventListener('DOMContentLoaded', () => {
    toggleSection('sectionA');
});
