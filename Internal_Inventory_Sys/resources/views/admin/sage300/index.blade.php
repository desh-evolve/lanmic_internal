@extends('layouts.admin')

@section('title', 'SAGE APIs')
@section('page-title', 'SAGE APIs')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item active">SAGE APIs</li>
@endsection

@section('content')
<style>
    pre {
        white-space: pre-wrap;
        word-wrap: break-word;
    }
</style>
<div class="row">
    <!-- API Request Card -->
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-paper-plane"></i> API Request</h3>
            </div>
            <div class="card-body">
                <form id="apiForm">
                    @csrf
                    <div class="form-group">
                        <label for="method">Method</label>
                        <select class="form-control" id="method" name="method">
                            <option value="GET">GET</option>
                            <option value="POST">POST</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="endpoint">Endpoint</label>
                        <select class="form-control" id="endpoint" name="endpoint">
                            @foreach($endpoints as $key => $value)
                                <option value="{{ $key }}">{{ $key }} - {{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="customEndpoint">Or Custom Endpoint</label>
                        <input type="text" class="form-control" id="customEndpoint" 
                               placeholder="e.g., APVendors('VENDOR001')">
                    </div>
                    
                    <div class="form-group" id="dataGroup" style="display:none;">
                        <label for="requestData">Request Data (JSON)</label>
                        <textarea class="form-control" id="requestData" rows="6" 
                                  placeholder='{"VendorNumber": "NEWVENDOR", "VendorName": "New Vendor"}'></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="filter">$filter (optional)</label>
                        <input type="text" class="form-control" id="filter" 
                               placeholder="e.g., VendorNumber eq 'VENDOR001'">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="top">$top (limit)</label>
                                <input type="number" class="form-control" id="top" value="50">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="skip">$skip (offset)</label>
                                <input type="number" class="form-control" id="skip" value="0">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block" id="sendBtn">
                        <i class="fas fa-paper-plane"></i> Send Request
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- API Response Card -->
    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-reply"></i> API Response</h3>
                <div class="card-tools">
                    <span class="badge badge-light" id="responseStatus"></span>
                </div>
            </div>
            <div class="card-body">
                <div id="loading" style="display:none;" class="text-center">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p>Loading...</p>
                </div>
                <pre id="responseData" class="bg-dark text-light p-3" 
                     style="max-height:500px; overflow:auto; border-radius:5px;">
Response will appear here...
                </pre>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Show/hide data field based on method
    $('#method').change(function() {
        if ($(this).val() === 'GET' || $(this).val() === 'DELETE') {
            $('#dataGroup').hide();
        } else {
            $('#dataGroup').show();
        }
    });
    
    // Handle form submission
    $('#apiForm').submit(function(e) {
        e.preventDefault();
        sendRequest();
    });
});

function sendRequest() {
    var method = $('#method').val();
    var endpoint = $('#customEndpoint').val() || $('#endpoint').val();
    var filter = $('#filter').val();
    var top = $('#top').val();
    var skip = $('#skip').val();
    
    var url = '';
    var data = {};
    
    switch(method) {
        case 'GET':
            url = '{{ route("sage300.api.get") }}';
            data = {
                endpoint: endpoint,
                '$filter': filter,
                '$top': top,
                '$skip': skip
            };
            break;
        case 'POST':
            url = '{{ route("sage300.api.post") }}';
            data = {
                endpoint: endpoint,
                data: JSON.parse($('#requestData').val() || '{}')
            };
            break;
    }
    
    $('#loading').show();
    $('#responseData').text('');
    $('#sendBtn').prop('disabled', true);
    
    $.ajax({
        url: url,
        type: method === 'GET' ? 'GET' : 'POST',
        data: method === 'GET' ? data : JSON.stringify(data),
        contentType: method === 'GET' ? 'application/x-www-form-urlencoded' : 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#loading').hide();
            $('#sendBtn').prop('disabled', false);
            $('#responseStatus').text('Status: ' + (response.status || 200)).removeClass('badge-danger').addClass('badge-success');
            $('#responseData').text(JSON.stringify(response, null, 2));
        },
        error: function(xhr) {
            $('#loading').hide();
            $('#sendBtn').prop('disabled', false);
            $('#responseStatus').text('Status: ' + xhr.status).removeClass('badge-success').addClass('badge-danger');
            $('#responseData').text(JSON.stringify(xhr.responseJSON || xhr.responseText, null, 2));
        }
    });
}

function quickLoad(endpoint) {
    $('#endpoint').val(endpoint);
    $('#customEndpoint').val('');
    $('#method').val('GET');
    $('#dataGroup').hide();
    sendRequest();
}
</script>
@endpush