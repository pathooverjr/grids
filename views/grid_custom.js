var grid, dialog;
var data;
data = [
                { 'ID': 1, 'Name': 'Hristo Stoichkov', 'PlaceOfBirth': 'Plovdiv, Bulgaria' },
                { 'ID': 2, 'Name': 'Ronaldo Luís Nazário de Lima', 'PlaceOfBirth': 'Rio de Janeiro, Brazil' },
                { 'ID': 3, 'Name': 'David Platt', 'PlaceOfBirth': 'Chadderton, Lancashire, England' },
                { 'ID': 4, 'Name': 'Manuel Neuer', 'PlaceOfBirth': 'Gelsenkirchen, West Germany' },
                { 'ID': 5, 'Name': 'James Rodríguez', 'PlaceOfBirth': 'Cúcuta, Colombia' },
                { 'ID': 6, 'Name': 'Dimitar Berbatov', 'PlaceOfBirth': 'Blagoevgrad, Bulgaria' }
            ];
function Edit(e) {
    $('#ID').val(e.data.id);
    $('#Name').val(e.data.record.Name);
    $('#PlaceOfBirth').val(e.data.record.PlaceOfBirth);
    dialog.open('Edit Player');
}
function Save() {
    var record = {
        ID: $('#ID').val(),
        Name: $('#Name').val(),
        PlaceOfBirth: $('#PlaceOfBirth').val()
    };
    $.ajax({ url: '/Players/Save', data: { record: record }, method: 'POST' })
        .done(function () {
            dialog.close();
            grid.reload();
        })
        .fail(function () {
            alert('Failed to save.');
            dialog.close();
        });
}
function Delete(e) {
    if (confirm('Are you sure?')) {
        $.ajax({ url: '/Players/Delete', data: { id: e.data.id }, method: 'POST' })
            .done(function () {
                grid.reload();
            })
            .fail(function () {
                alert('Failed to delete.');
            });
    }
}
$(document).ready(function () {
    grid = $('#grid').grid({
        primaryKey: 'ID',
        dataSource: data,
        uiLibrary: 'bootstrap4',
        columns: [
            { "field": "ID", "width": "48" },
            { field: 'Name', sortable: true },
            { field: 'PlaceOfBirth', title: 'Place Of Birth', sortable: true },
            { title: '', field: 'Edit', width: 42, type: 'icon', icon: 'fa fa-pencil', tooltip: 'Edit', events: { 'click': Edit } },
            { title: '', field: 'Delete', width: 42, type: 'icon', icon: 'fa fa-remove', tooltip: 'Delete', events: { 'click': Delete } }
        ],
        pager: { limit: 5, sizes: [2, 5, 10, 20] }
    });
    dialog = $('#dialog').dialog({
        uiLibrary: 'bootstrap4',
        iconsLibrary: 'fontawesome',
        autoOpen: false,
        resizable: false,
        modal: true
    });
    $('#btnAdd').on('click', function () {
        $('#ID').val('');
        $('#Name').val('');
        $('#PlaceOfBirth').val('');
        dialog.open('Add Player');
    });
    $('#btnSave').on('click', Save);
    $('#btnCancel').on('click', function () {
        dialog.close();
    });
    $('#btnSearch').on('click', function () {
        grid.reload({ name: $('#txtName').val(), placeOfBirth: $('#txtPlaceOfBirth').val() });
    });
    $('#btnClear').on('click', function () {
        $('#txtName').val('');
        $('#txtPlaceOfBirth').val('');
        grid.reload({ name: '', placeOfBirth: '' });
    });
});