<?php
// Port of data.js themes — colors preserved exactly.
const THEMES = [
  'dark_romantic' => [
    'name' => 'Dark Romantic',
    'bg' => '#16140f', 'bg2' => '#1e1b14', 'surface' => '#26221a',
    'border' => 'rgba(201,173,135,0.18)',
    'accent' => '#c9ad87', 'accentDark' => '#a8895f',
    'text' => '#faf8f4', 'muted' => 'rgba(250,248,244,0.45)',
    'preview' => ['#16140f','#c9ad87','#faf8f4'],
  ],
  'cream_gold' => [
    'name' => 'Cream & Gold',
    'bg' => '#faf6ef', 'bg2' => '#f3ede4', 'surface' => '#ffffff',
    'border' => '#e6d9c8',
    'accent' => '#b8965a', 'accentDark' => '#9a7a44',
    'text' => '#2a2420', 'muted' => '#8a7a6a',
    'preview' => ['#faf6ef','#b8965a','#2a2420'],
  ],
  'sage_garden' => [
    'name' => 'Sage Garden',
    'bg' => '#f0f4ef', 'bg2' => '#e6ede5', 'surface' => '#ffffff',
    'border' => '#c8d9c4',
    'accent' => '#6b8f71', 'accentDark' => '#527358',
    'text' => '#1e2c1e', 'muted' => '#6a826a',
    'preview' => ['#f0f4ef','#6b8f71','#1e2c1e'],
  ],
  'dusty_rose' => [
    'name' => 'Dusty Rose',
    'bg' => '#fdf4f4', 'bg2' => '#f7eaea', 'surface' => '#ffffff',
    'border' => '#e8cece',
    'accent' => '#c4788a', 'accentDark' => '#a85f72',
    'text' => '#2a1820', 'muted' => '#8a6870',
    'preview' => ['#fdf4f4','#c4788a','#2a1820'],
  ],
  'midnight_blue' => [
    'name' => 'Midnight Blue',
    'bg' => '#0f1525', 'bg2' => '#161e30', 'surface' => '#1e2840',
    'border' => 'rgba(141,164,200,0.2)',
    'accent' => '#8da4c8', 'accentDark' => '#6d88b0',
    'text' => '#e8edf5', 'muted' => 'rgba(232,237,245,0.45)',
    'preview' => ['#0f1525','#8da4c8','#e8edf5'],
  ],
];

function theme(string $key): array {
  return THEMES[$key] ?? THEMES['dark_romantic'];
}

function themeCss(string $key): string {
  $t = theme($key);
  return ":root{"
    . "--bg:{$t['bg']};--bg2:{$t['bg2']};--surface:{$t['surface']};"
    . "--border:{$t['border']};--accent:{$t['accent']};--accent-dark:{$t['accentDark']};"
    . "--text:{$t['text']};--muted:{$t['muted']};"
    . "}";
}
