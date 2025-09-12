@extends('correos.layout')

@section('contenido')
<div>
    <h1>Sistema de alquileres</h1>
    <h2>Recuperación de contraseña</h2>
    <p>Para recuperar tu contraseña, utiliza el siguiente código:</p>
    <div class="alert alert-success" role="alert">
        <strong>{{ $codigo }}</strong>
    </div>
    <p>Si no solicitaste este cambio, ignora este mensaje.</p>
@endsection