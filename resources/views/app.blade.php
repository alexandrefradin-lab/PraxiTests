<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'PraxiQuest') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|dm-sans:300,400,500,600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base:              #F0E8D4;
            --bg-surface:           #E5DAC2;
            --bg-elevated:          #D8CEB5;
