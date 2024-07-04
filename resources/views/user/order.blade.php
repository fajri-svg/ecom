@extends('layouts.app')
@section('title')
    Order
@endsection
@section('content')
    <div class="page-title">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <ul class="breadcrumbs">
                        <li><a href="/">Home</a> </li>
                        <li class="separator"></li>
                        <li>Orders</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="container padding-bottom-3x mb-1">
        <div class="row">
            @include('includes.user-sidebar')
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="u-table-res">
                            <table id="order-table" class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Total</th>
                                        <th>Order Status</th>
                                        <th>Payment Status</th>
                                        <th>Date Purchased</th>
                                        <th><center>Action</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $index = 0; @endphp
                                    @foreach ($orders as $order)
                                    <tr class="order-row {{ $index >= 6 ? 'd-none' : '' }}">
                                        <td><a class="navi-link" href="#" data-toggle="modal"
                                                data-target="#orderDetails">{{ $order->uuid }}</a></td>
                                        <td>{{ format_rupiah($order->total_amount) }}</td>
                                        <td><span class="text-info">{{ $order->order_status }}</span></td>
                                        <td><span class="text-success">{{ $order->payment_status }}</span></td>
                                        <td>{{ \Carbon\Carbon::parse($order->created_at)->format('H:i:s - d M Y') }}</td>
                                        <td>
                                            <div class="btn-group">
                                                {{-- <a href="{{ route('user.order.invoice', $order->uuid) }}"
                                                    class="btn btn-info btn-sm">Invoice</a> --}}
                                                <a href="{{ route('user.order.invoice.pdf', $order->uuid) }}"
                                                    class="btn btn-primary btn-sm">Invices</a>

                                                        <div class="dropdown">
                                                            <button class="btn btn-sm dropdown-toggle {{ $order->order_status === 'Pending' ? 'btn-warning' : ($order->order_status === 'In Progress' ? 'btn-info' : ($order->order_status === 'Delivered' ? 'btn-success' : 'btn-danger')) }}" type="button"
                                                                id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                {{ $order->order_status }}
                                                            </button>
                                                            <div class="dropdown-menu animated--fade-in" aria-labelledby="dropdownMenuButton">
                                                                {{-- <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#statusModal"
                                                                    href="javascript:;" data-href="{{ route('admin.order.change.pending.status', ['id'=>$order->id]) }}">Pending</a>
                                                                <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#statusModal"
                                                                    href="javascript:;" data-href="{{ route('admin.order.change.progress.status', ['id'=>$order->id]) }}">In Progress</a>
                                                                <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#statusModal"
                                                                    href="javascript:;" data-href="{{ route('admin.order.change.delivered.status', ['id'=>$order->id]) }}">Delivered</a> --}}
                                                                <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cancelOrderModal"
                                                                    href="javascript:;" data-order-id="{{ $order->id }}">Cancelled</a>
                                                            </div>
                                                        </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @php $index++; @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <button id="prev-slide" class="btn btn-primary btn-sm">&lt; Previous</button>
                            <button id="next-slide" class="btn btn-primary btn-sm">Next &gt;</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal untuk konfirmasi pembatalan pesanan -->
            <div class="modal fade" id="cancelOrderModal" tabindex="-1" role="dialog" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form id="cancelOrderForm" action="{{ route('user.order.cancel') }}" method="POST">
                            @csrf
                            <input type="hidden" id="order_id" name="order_id">
                            <div class="modal-header">
                                <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order Confirmation</h5>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to cancel this order?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-danger">Cancel Order</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>



        </div>
    </div>
    <script>
        $(document).ready(function() {
    $('#cancelOrderModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var orderId = button.data('order-id');
        var modal = $(this);
        modal.find('#order_id').val(orderId);
    });

    // Mengirim form untuk membatalkan pesanan
    $('#cancelOrderForm').on('submit', function(event) {
        event.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $('#cancelOrderModal').modal('hide');
                alert(response.message);
                location.reload(); // Refresh halaman untuk memperbarui status order
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert('Order cannot be cancelled.');
            }
        });
    });
});


    </script>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#order-table');
        var rows = table.find('tbody tr');
        var index = 0;

        // Hide rows beyond the fifth row initially
        rows.slice(6).addClass('d-none');

        $('#prev-slide').click(function() {
            if (index > 0) {
                index--;
                showRows();
            }
        });

        $('#next-slide').click(function() {
            if (index < Math.ceil(rows.length / 6) - 1) {
                index++;
                showRows();
            }
        });

        function showRows() {
            var start = index * 6;
            var end = start + 6;
            rows.addClass('d-none');
            rows.slice(start, end).removeClass('d-none');
        }
    });
</script>




