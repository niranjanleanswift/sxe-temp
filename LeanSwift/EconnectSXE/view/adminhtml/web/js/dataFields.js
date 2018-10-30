define(['jquery', 'mage/storage','domReady!'], function ($, Storage) {
    'use strict';

    /**
     * Initializer
     * @param {String} url
     */
    function init(url) {
        var WarehouseRetrieveButton  = $('#econnectSXE_basic_data_datafield_button');
        var WarehouseSelectList     = $('#econnectSXE_basic_data_warehouse');
        WarehouseRetrieveButton.click(function () {
            $('body').trigger('processStart');
            Storage.get(url).success(function(result){
                $('body').trigger('processStop');
                if(result) {
                    WarehouseSelectList.empty();
                    $.each(result, function(i, obj){
                        WarehouseSelectList.append($('<option>').text(obj.label).attr('value', obj.value));
                    });
                }
            });
        });
    }

    /**
     * Export/return dataFields
     * @param {Object} dataFields
     */
    return function (dataFields) {
        init(dataFields.url);
    };
});
