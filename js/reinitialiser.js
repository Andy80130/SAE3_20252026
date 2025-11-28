function reinitialiserDonnees() {
    const form = document.querySelector('.trip-form'); //selecteur du formulaire

    form.querySelectorAll('input, select').forEach(element => {
        if (element.type === 'checkbox' || element.type === 'radio') {
            element.checked = false;
        } else {
            element.value = '';
        }
    });

    const autoFields = ['depart', 'destination', 'depart_lat', 'depart_lon', 'destination_lat', 'destination_lon'];
    autoFields.forEach(id => {
        const element = document.getElementById(id);
        if (element) element.value = '';
    });
}