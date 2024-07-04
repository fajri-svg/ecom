<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
        /* Styles for the invoice */
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @if (!function_exists('format_rupiah'))
    @php
        function format_rupiah($number) {
            return 'Rp' . number_format($number, 0, ',', '.');
        }
    @endphp
@endif
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <h1><center>Invoice</center></h1>
                            </td>
                            {{-- <td>
                                Invoice #: {{ $order->uuid }}<br>
                                Created: {{ $order->created_at->format('H:i:s - d/m/Y') }}<br>
                                Due: {{ $order->created_at->addDays(30)->format('d/m/Y') }}
                            </td> --}}
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <strong>Customer Name: </strong>{{ $user->name }}<br>
                                <strong>Customer Email: </strong>{{ $user->email }}<br>
                                <strong>Customer Telp: </strong>{{ $user->phone }}<br>

                                @if(isset($billing_address))
                                    <strong style="text-transform:">Nama Company: </strong>{{ $billing_address->company }}<br>
                                    <strong style="text-transform:"> Address 1: </strong>{{ $billing_address->address1 }}<br>
                                    <strong style="text-transform:">Address 2: </strong>{{ $billing_address->address2 }}
                                @else
                                    Tidak Ada Alamat
                                @endif
                            </td>
                            <td>
                                Invoice #: {{ $order->uuid }}<br>
                                Created: {{ $order->created_at->format('H:i:s - d/m/Y') }}<br>
                                Due: {{ $order->created_at->addDays(30)->format('d/m/Y') }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td>
                    Payment Status
                </td>
                <td>
                    Status
                </td>
            </tr>

            <tr class="details">
                <td>

                </td>
                <td>
                    <strong style="text-transform: uppercase;">{{ strtoupper($order->payment_status) }}</strong>
                </td>
            </tr>

            <tr class="heading">
                <td>
                    Order Status
                </td>
                <td>
                    Status
                </td>
            </tr>

            <tr class="details">
                <td>

                </td>
                <td>
                    <strong style="text-transform: uppercase;">{{ strtoupper($order->order_status) }}</strong>
                </td>
            </tr>

            <tr class="heading">
                <td>
                    Payment Method
                </td>
                <td>
                    {{ $order->payment_method }}
                </td>
            </tr>

            <tr class="details">
                <td>
                </td>
                <td>
                </td>
            </tr>

            <tr class="heading">
                <td>
                    Item
                </td>
                <td>
                    Qty x Price
                </td>
            </tr>

            @foreach($products as $index => $product)
                <tr class="item">
                    <td>
                        {{ App\Models\Product::find($product)->name }}
                    </td>
                    <td>
                        @if(isset(json_decode($order->qty)[$index]))
                        {{ json_decode($order->qty)[$index] }} x <!-- Menampilkan qty -->
                    @endif
                        {{ format_rupiah(App\Models\Product::find($product)->current_price) }}
                    </td>
                </tr>
            @endforeach


            <tr class="total">
                <td>

                </td>
                <td>
                   Total: {{ format_rupiah($order->total_amount) }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
