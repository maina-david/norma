import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";

dayjs.extend(relativeTime);

const dateFormats = {
  date: (ts) => dayjs(ts * 1000).format('DD MMM YYYY'),
  time: (ts) => dayjs(ts * 1000).format('HH:mm'),
  datetime: (ts) => dayjs(ts * 1000).format('DD MMM YYYY, HH:mm'),
  diff: (ts) => dayjs().to(dayjs(ts * 1000)),
};

window.initDayJs = (node) => {
  const selector = Object.keys(dateFormats).map(item => `[data-timestamp][data-type="${item}"]`).join(',');

  node.querySelectorAll(selector).forEach((el) => {
    el.innerText = dateFormats[el.getAttribute('data-type')](el.getAttribute('data-timestamp'));
  });
};

