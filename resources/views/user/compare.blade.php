@extends('layouts.app')
@section('title')
    Compare
@endsection
@section('content')
    <div class="page-title">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <ul class="breadcrumbs">
                        <li><a href="/">Home</a> </li>
                        <li class="separator"></li>
                        <li>Compare</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="container padding-bottom-3x mb-1">
        <div class="card">
            <div class="card-body">
                <div class="comparison-table">
                    <table class="table table-bordered">

                        <tbody>
                            <th>Nama Product</th>
                            <th>Harga Product</th>
                            <th>Gambar Product</th>
                            <th class="text-center">
                                Action:
                                <a class="btn btn-sm btn-primary" href="{{ route('user.compare.clear') }}"><span>  Clear
                                Compare</span></a>
                            </th>
                            @foreach ($compares as $compare)
                                <tr>
                                    <td style="text-align: center; vertical-align: middle;">{{ $compare->product->name }}</td>
                                    <td style="text-align: center; vertical-align: middle;">${{ $compare->product->current_price }}</td>
                                    <td style="text-align: center; vertical-align: middle;"><img src="{{ asset('uploads/product') }}/{{ $compare->product->featured_image }}" width="80" alt=""></td>
                                    <td style="text-align: center; vertical-align: middle;"><a class="btn btn-success" href="{{ route('user.add_to_cart', ['id'=>$compare->product->id]) }}">cart</a></td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
