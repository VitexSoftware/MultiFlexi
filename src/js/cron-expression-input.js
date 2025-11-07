/**
 * Cron Expression Validator and Input Web Component
 * Provides a browser-compatible validator and a custom input UI.
 * All code is unminified and maintainable.
 */

/**
 * Validate a cron expression.
 * @param {string} cron - The cron expression to validate.
 * @param {object} options - Validation options.
 * @returns {boolean} True if valid, false otherwise.
 */
function isValidCron(cron, options = {}) {
    const defaultOptions = {
        alias: false,
        seconds: false,
        allowBlankDay: false,
        allowSevenAsSunday: false
    };
    options = Object.assign({}, defaultOptions, options);
    const monthAlias = {jan: '1', feb: '2', mar: '3', apr: '4', may: '5', jun: '6', jul: '7', aug: '8', sep: '9', oct: '10', nov: '11', dec: '12'};
    const weekdaysAlias = {sun: '0', mon: '1', tue: '2', wed: '3', thu: '4', fri: '5', sat: '6'};
    function safeParseInt(value) { return /^\d+$/.test(value) ? Number(value) : NaN; }
    function isWildcard(value) { return value === '*'; }
    function isQuestionMark(value) { return value === '?'; }
    function isInRange(value, start, stop) { return value >= start && value <= stop; }
    function isValidRange(value, start, stop) {
        const sides = value.split('-');
        if (sides.length === 1) return isWildcard(value) || isInRange(safeParseInt(value), start, stop);
        if (sides.length === 2) {
            const [small, big] = sides.map(safeParseInt);
            return small <= big && isInRange(small, start, stop) && isInRange(big, start, stop);
        }
        return false;
    }
    function isValidStep(value) { return value === undefined || value.search(/[^\d]/) === -1; }
    function validateForRange(value, start, stop) {
        if (value.search(/[^\d-,\/*]/) !== -1) return false;
        return value.split(',').every(condition => {
            const splits = condition.split('/');
            if (condition.trim().endsWith('/')) return false;
            if (splits.length > 2) return false;
            const [left, right] = splits;
            return isValidRange(left, start, stop) && isValidStep(right);
        });
    }
    function hasValidSeconds(seconds) { return validateForRange(seconds, 0, 59); }
    function hasValidMinutes(minutes) { return validateForRange(minutes, 0, 59); }
    function hasValidHours(hours) { return validateForRange(hours, 0, 23); }
    function hasValidDays(days, allowBlankDay) { return (allowBlankDay && isQuestionMark(days)) || validateForRange(days, 1, 31); }
    function hasValidMonths(months, alias) {
        if (months.search(/\/[a-zA-Z]/) !== -1) return false;
        if (alias) {
            const remapped = months.toLowerCase().replace(/[a-z]{3}/g, m => monthAlias[m] || m);
            return validateForRange(remapped, 1, 12);
        }
        return validateForRange(months, 1, 12);
    }
    function hasValidWeekdays(weekdays, alias, allowBlankDay, allowSevenAsSunday) {
        if (allowBlankDay && isQuestionMark(weekdays)) return true;
        if (!allowBlankDay && isQuestionMark(weekdays)) return false;
        if (weekdays.search(/\/[a-zA-Z]/) !== -1) return false;
        if (alias) {
            const remapped = weekdays.toLowerCase().replace(/[a-z]{3}/g, m => weekdaysAlias[m] || m);
            return validateForRange(remapped, 0, allowSevenAsSunday ? 7 : 6);
        }
        return validateForRange(weekdays, 0, allowSevenAsSunday ? 7 : 6);
    }
    function hasCompatibleDayFormat(days, weekdays, allowBlankDay) {
        return !(allowBlankDay && isQuestionMark(days) && isQuestionMark(weekdays));
    }
    function split(cron) { return cron.trim().split(/\s+/); }
    const splits = split(cron);
    if (splits.length > (options.seconds ? 6 : 5) || splits.length < 5) return false;
    let checks = [];
    let s = splits.slice();
    if (splits.length === 6) {
        const seconds = s.shift();
        if (seconds) checks.push(hasValidSeconds(seconds));
    }
    const [minutes, hours, days, months, weekdays] = s;
    checks.push(hasValidMinutes(minutes));
    checks.push(hasValidHours(hours));
    checks.push(hasValidDays(days, options.allowBlankDay));
    checks.push(hasValidMonths(months, options.alias));
    checks.push(hasValidWeekdays(weekdays, options.alias, options.allowBlankDay, options.allowSevenAsSunday));
    checks.push(hasCompatibleDayFormat(days, weekdays, options.allowBlankDay));
    return checks.every(Boolean);
}

/**
 * <cron-expression-input> Web Component
 * Minimal UI for cron input and validation.
 */
class CronExpressionInput extends HTMLElement {
    /**
     * Initialize the component and attach shadow DOM.
     */
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
    }

    /**
     * Called when the element is added to the DOM.
     * Renders the input and sets up validation.
     */
    connectedCallback() {
        this.render();
        this.input = this.shadowRoot.querySelector('input');
        this.error = this.shadowRoot.querySelector('.cronexpressionError');
        this.input.addEventListener('input', () => this.validate());
    }

    /**
     * Render the input field and error message.
     */
    render() {
        this.shadowRoot.innerHTML = `
            <style>
                .cronexpressionError { color: #c00; display: none; }
                .cronexpressionError.visible { display: block; }
            </style>
            <input type="text" class="form-control" placeholder="Cron Expression">
            <small class="cronexpressionError">Invalid cron expression, try with (* * * * *)</small>
        `;
    }

    /**
     * Validate the input value and show/hide error.
     */
    validate() {
        const value = this.input.value;
        if (!isValidCron(value)) {
            this.error.classList.add('visible');
        } else {
            this.error.classList.remove('visible');
        }
    }
}

// Register the custom element
customElements.define('cron-expression-input', CronExpressionInput);