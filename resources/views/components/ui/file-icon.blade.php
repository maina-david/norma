@props(['mimeType'])

@php
switch ($mimeType) {
    case 'image/jpeg':
    case 'image/png':
        $icon = 'image';
        break;
    case 'application/pdf':
        $icon = 'file-pdf';
        break;
    case 'application/vnd.ms-excel':
    case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
        $icon = 'file-excel';
        break;
    case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
    case 'application/msword':
        $icon = 'file-word';
        break;
    case 'application/zip':
    case 'application/octet-stream':
    case 'application/x-rar-compressed':
        $icon = 'file-archive';
        break;
    case 'text/plain':
        $icon = 'file-alt';
        break;
    default:
        $icon = 'file';
        break;
}
@endphp

<x-ui.icon :name="$icon" {{ $attributes }} />
