import moment from 'moment/moment';

export default {
    install: (app, options) => {
        app.config.globalProperties.$helpers = {

            formatDate(date, format = 'DD.MM.YYYY') {
                if(date && moment(date)) {
                    return moment(date).format(format);
                }
            },

            formatDateTime(date) {
                return this.formatDate(date);
            },

            formatDateRange(from, to) {

                if(moment(from).isSame(moment(to), 'hour')) {
                    return moment(from).format('DD.MM.YYYY HH:mm');
                }

                if(moment(from).isSame(moment(to), 'day')) {
                    return moment(from).format('DD.MM.YYYY HH:mm')+' - '+moment(to).format('HH:mm');
                }

                return moment(from).format('DD.MM.YYYY HH:mm')+' - '+moment(to).format('DD.MM.YYYY HH:mm');
            },

            formatCurrency(value, currency = '€') {
                return parseFloat(value)
                  .toLocaleString('de-AT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })+' '+currency;
            },

            formatPercent(percent) {
                
                const austrianFormat = (number) => {
                    return Number(number).toLocaleString('de-AT', {
                        minimumFractionDigits: 2, 
                        maximumFractionDigits: 2
                    });
                };

                return `${austrianFormat(percent)}%`;
            },

            calculateFinancingAmount(percent, projectCostsVal) {
                const projectCosts = parseFloat(projectCostsVal);
                const percentage = parseFloat(percent.toString().replace(',', '.'));
            
                if (!isNaN(projectCosts) && !isNaN(percentage)) {
                  return ((projectCosts / 100) * (percentage)).toFixed(2); // Two decimal precision
                } else {
                  return 0;
                }
              },

            stripHTML(html) {
                let tmp = document.createElement('div');
                tmp.innerHTML = html;

                return (tmp.textContent || tmp.innerText || '').trim();
            },

            textExcerpt(text, length = 256, ellipsis = '...') {

                if(!text || text.length <= length - ellipsis.length) {
                    return text;
                }

                return text.slice(0, length - ellipsis.length).trim() + ellipsis;

            },

            sleep (ms = 1000) {
                return new Promise(resolve => setTimeout(resolve, ms));
            },

        };
    }
}