import { Chart, CategoryScale, LinearScale, Legend, BarController, BarElement, PieController, ArcElement, Tooltip, LineController, LineElement, PointElement } from 'chart.js';


Chart.register(
  CategoryScale,
  LinearScale,
  Legend,
  BarController,
  BarElement,
  PieController,
  ArcElement,
  Tooltip,
  LineController,
  LineElement,
  PointElement);
window.ChartJs = Chart;
