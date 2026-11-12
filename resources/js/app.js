import './bootstrap';
import './modules/theme';
import { initSpaNavigator } from './modules/spa-navigator';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Aktifkan SPA Partial Content Navigation (Instant menu change & search filter)
initSpaNavigator();

