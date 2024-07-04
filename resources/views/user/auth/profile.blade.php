@extends('layouts.app')
@section('title')
    Profie
@endsection
@section('content')
    <div class="page-title">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <ul class="breadcrumbs">
                        <li><a href="/">Home</a> </li>
                        <li class="separator"></li>
                        <li>Profile</li>
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
                        <div class="padding-top-2x mt-2 hidden-lg-up"></div>
                        <form class="row" action="{{ route('user.profile.update') }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            {{-- <div  class="col-lg-6">
                                <div class="form-group">
                                    <label for="photo">Current Image</label>
                                    <div class="col-lg-4 pb-1">
                                        <img class="admin-img"
                                        src="{{ asset('uploads/' . Auth::user()->photo) }}"
                                        alt="No Image Found">
                                    </div>
                                    <span>Image Size Should Be 40 x 40.</span>
                                </div>

                                <div class="form-group position-relative">
                                    <label class="file">
                                        <input type="file" accept="image/*" class="upload-photo"
                                        name="photo" id="avater" aria-label="File browser example">
                                        <span class="file-custom text-left">Upload Image...</span>
                                    </label>
                                    @error('photo')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div> --}}

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="photo">Current Image</label>
                                    <div class="col-lg-6 pb-1">
                                        <img class="admin-setting-img" src="{{ asset('uploads/profile/' . Auth::user()->photo) }}" alt="No Image Found">
                                    </div>
                                    <span>Image Size Should Be 140 x 140.</span>
                                </div>
                                <input type="hidden" name="old_photo" value="{{ Auth::user()->photo }}">

                                <div class="form-group position-relative">
                                    <label class="file">
                                        <input type="file" accept="image/*" class="upload-photo" name="photo" id="avater" aria-label="File browser example">
                                        <span class="file-custom text-left">Upload Image...</span>
                                    </label>
                                </div>
                            </div>


                            @php
                                $name=explode(' ',Auth::user()->name);
                            @endphp
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account-fn">First Name</label>
                                    <input class="form-control"  name="first_name" type="text" id="account-fn"
                                        value="{{ $name[0] }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account-ln">Last Name</label>
                                    <input class="form-control" type="text" name="last_name" id="account-ln"
                                        value="{{ $name[1] }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account-email">E-mail Address</label>
                                    <input class="form-control" name="email" type="email" id="account-email"
                                        value="{{ Auth::user()->email }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account-phone">Phone Number</label>
                                    <input class="form-control" name="phone" type="text" id="account-phone"
                                        value="{{ Auth::user()->phone }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account-pass">New Password</label>
                                    <input class="form-control" name="password" type="text" id="account-pass"
                                        placeholder="Change your password">
                                </div>
                            </div>
                            <div class="col-12">
                                <hr class="mt-2 mb-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-center">
                                    <button class="btn btn-primary margin-right-none" type="submit"><span>Update
                                            Profile</span></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
