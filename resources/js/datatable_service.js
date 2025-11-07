BxCoder.Datatable = (function(document){
    var dataTable;
    var currentRow;
    var initDataTable = function(selector, options = {}) {
        var defaults = {
            pageLength: 9,
            order: [[1, 'desc']],
            processing: true,
            serverSide: false
        };
        var settings = $.extend(true, {}, defaults, options);
        dataTable = $(selector).DataTable(settings);
    }

    var selectRow = function (row){
        currentRow = $(row).parents('tr');
    }

    var validateDatatable = function (){
        if(dataTable === undefined || typeof (dataTable) === "undefined"){
            toastr.error("Datatable is not initialized. Please reload the page.");
            return false;
        }

        return true;
    }

    var validateCurrentRow = function (){
        if(currentRow === undefined || typeof (currentRow) === "undefined"){
            toastr.error("Datatable row is not initialized. Please reload the page.");
            return false;
        }

        return true;
    }

    var addNewRow = function(row, isHighlight = true) {
        if (!validateDatatable()) return;

        let newNode = dataTable.row.add(row).draw(false).node(); // get <tr>

        const allRows = dataTable.rows({ order: 'applied' }).data().toArray();

        const index = allRows.findIndex((item) => {
            return JSON.stringify(item) === JSON.stringify(row);
        });

        if (index !== -1) {
            const pageSize = dataTable.page.info().length;
            const pageToGo = Math.floor(index / pageSize);

            dataTable.page(pageToGo).draw(false);

            if (isHighlight) {
                $(newNode).addClass('table-active');
                setTimeout(() => { $(newNode).removeClass('table-active'); }, 2000);
            }
        }

        return newNode;
    }


    var updateNewRow = function (rowInfo, isHighlight){
        deleteRow();
        return addNewRow(rowInfo, isHighlight);
    }

    var deleteRow = function (){
        if(!validateDatatable() || !validateCurrentRow()) return;
        dataTable.row($(currentRow)).remove().draw();
    }

    var filter = function(value){
        dataTable.search(value).draw();
    }

    var refresh = function(){
        if (!validateDatatable()) return;
        dataTable.ajax.reload(null, false);
    }

    var getDataTable = function(){
        return dataTable;
    }

    return {
        initDataTable: initDataTable,
        selectRow: selectRow,
        addNewRow: addNewRow,
        updateNewRow: updateNewRow,
        deleteRow: deleteRow,
        filter: filter,
        refresh: refresh,
        getDataTable: getDataTable
    }
})(document);
