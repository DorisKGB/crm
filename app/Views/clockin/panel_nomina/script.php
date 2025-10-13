<script>
    

    flatpickr("#fromDate", {
        dateFormat: "Y-m-d"
    });
    flatpickr("#toDate", {
        dateFormat: "Y-m-d"
    });

    document.getElementById("formNomina").addEventListener("submit", function(e) {
        e.preventDefault();
        const from = document.getElementById("fromDate").value;
        const to = document.getElementById("toDate").value;
        const userId = <?= json_encode($login_user->id) ?>;

        const params = new URLSearchParams(window.location.search);
        params.set('from', from);
        params.set('to', to);
        params.set('user_id', userId);
        params.set('option', 'nomina');

        window.location.href = "<?= site_url('clockin') ?>?" + params.toString();
    });
</script>