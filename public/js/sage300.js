// public/js/sage300.js

const Sage300 = {
    baseUrl: '../admin/sage300/api',
    
    get: function(endpoint, params = {}) {
        params.endpoint = endpoint;
        return $.ajax({
            url: this.baseUrl + '/get',
            type: 'GET',
            data: params
        });
    },
    
    post: function(endpoint, data) {
        return $.ajax({
            url: this.baseUrl + '/post',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                endpoint: endpoint,
                data: data
            }),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    },
    
    // Helper methods for items
    getItems: function() {
        return $.get(this.baseUrl + '/items');
    },
    
    getItemDetails: function(code) {
        return $.get(this.baseUrl + '/items/' + code);
    },
    
    getItemLocations: function(itemCode) {
        return $.get(this.baseUrl + '/items/' + itemCode + '/locations');
    },
    
    // Helper methods for locations
    getLocations: function() {
        return $.get(this.baseUrl + '/locations');
    },
    
    // Utility methods
    refreshItemPrice: function(code) {
        return this.getItemDetails(code);
    }
};