import './bootstrap';
import './theme'
import './index-charts';
import './charts-demo';


import * as Popper from '@popperjs/core';
import 'bootstrap';

import Chart from 'chart.js/auto';


// Example: Make Chart.js available globally
window.Chart = Chart;
window.Popper = Popper;


import '../css/portal.scss';