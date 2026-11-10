@extends('layouts/blankLayout')

@section('title', 'Login - Pages')

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <!-- Register -->
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <a href="{{ url('/') }}" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo">@include('_partials.macros')</span>
                                <span
                                    class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span>
                            </a>
                        </div>
                        <!-- /Logo -->
                        <h4 class="mb-1">Welcome to {{ config('variables.templateName') }}!</h4>
                        <p class="mb-6">Please sign-in to your account and start the adventure</p>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form id="formAuthentication" class="mb-6" action="{{ route('login.process') }}" method="POST">
                            @csrf
                            <div class="mb-6">
                                <label for="pgw_nip" class="form-label">NIP</label>
                                <input type="text" class="form-control @error('pgw_nip') is-invalid @enderror" id="pgw_nip"
                                    name="pgw_nip" value="{{ old('pgw_nip') }}" placeholder="Masukkan NIP Anda" autofocus />
                            </div>
                            <div class="mb-6 form-password-toggle">
                                <label class="form-label" for="pgw_password">Password</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="pgw_password"
                                        class="form-control @error('pgw_password') is-invalid @enderror" name="pgw_password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                        aria-describedby="pgw_password" />
                                    <span class="input-group-text cursor-pointer"><i
                                            class="icon-base bx bx-hide"></i></span>
                                </div>
                            </div>
                            <div class="mb-8">
                                <div class="d-flex justify-content-between">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="remember-me" name="remember" />
                                        <label class="form-check-label" for="remember-me"> Remember Me </label>
                                    </div>

                                </div>
                            </div>
                            <div class="mb-6">
                                <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
                            </div>
                        </form>


                    </div>
                </div>
                <!-- /Register -->
            </div>
        </div>
    </div>
@endsection