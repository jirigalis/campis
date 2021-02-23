var __assign =
        (this && this.__assign) ||
        function () {
            return (__assign =
                Object.assign ||
                function (t) {
                    for (var i, a = 1, s = arguments.length; a < s; a++)
                        for (var n in (i = arguments[a])) Object.prototype.hasOwnProperty.call(i, n) && (t[n] = i[n]);
                    return t;
                }).apply(this, arguments);
        },
    CountUp = (function () {
        function t(t, i, a) {
            var s = this;
            (this.target = t),
                (this.endVal = i),
                (this.options = a),
                (this.version = '2.0.5'),
                (this.defaults = {
                    startVal: 0,
                    decimalPlaces: 0,
                    duration: 2,
                    useEasing: !0,
                    useGrouping: !0,
                    smartEasingThreshold: 999,
                    smartEasingAmount: 333,
                    separator: ',',
                    decimal: '.',
                    prefix: '',
                    suffix: '',
                }),
                (this.finalEndVal = null),
                (this.useEasing = !0),
                (this.countDown = !1),
                (this.error = ''),
                (this.startVal = 0),
                (this.paused = !0),
                (this.count = function (t) {
                    s.startTime || (s.startTime = t);
                    var i = t - s.startTime;
                    (s.remaining = s.duration - i),
                        s.useEasing
                            ? s.countDown
                                ? (s.frameVal = s.startVal - s.easingFn(i, 0, s.startVal - s.endVal, s.duration))
                                : (s.frameVal = s.easingFn(i, s.startVal, s.endVal - s.startVal, s.duration))
                            : s.countDown
                            ? (s.frameVal = s.startVal - (s.startVal - s.endVal) * (i / s.duration))
                            : (s.frameVal = s.startVal + (s.endVal - s.startVal) * (i / s.duration)),
                        s.countDown
                            ? (s.frameVal = s.frameVal < s.endVal ? s.endVal : s.frameVal)
                            : (s.frameVal = s.frameVal > s.endVal ? s.endVal : s.frameVal),
                        (s.frameVal = Math.round(s.frameVal * s.decimalMult) / s.decimalMult),
                        s.printValue(s.frameVal),
                        i < s.duration
                            ? (s.rAF = requestAnimationFrame(s.count))
                            : null !== s.finalEndVal
                            ? s.update(s.finalEndVal)
                            : s.callback && s.callback();
                }),
                (this.formatNumber = function (t) {
                    var i,
                        a,
                        n,
                        e,
                        r,
                        o = t < 0 ? '-' : '';
                    if (
                        ((i = Math.abs(t).toFixed(s.options.decimalPlaces)),
                        (n = (a = (i += '').split('.'))[0]),
                        (e = a.length > 1 ? s.options.decimal + a[1] : ''),
                        s.options.useGrouping)
                    ) {
                        r = '';
                        for (var l = 0, h = n.length; l < h; ++l)
                            0 !== l && l % 3 == 0 && (r = s.options.separator + r), (r = n[h - l - 1] + r);
                        n = r;
                    }
                    return (
                        s.options.numerals &&
                            s.options.numerals.length &&
                            ((n = n.replace(/[0-9]/g, function (t) {
                                return s.options.numerals[+t];
                            })),
                            (e = e.replace(/[0-9]/g, function (t) {
                                return s.options.numerals[+t];
                            }))),
                        o + s.options.prefix + n + e + s.options.suffix
                    );
                }),
                (this.easeOutExpo = function (t, i, a, s) {
                    return (a * (1 - Math.pow(2, (-10 * t) / s)) * 1024) / 1023 + i;
                }),
                (this.options = __assign(__assign({}, this.defaults), a)),
                (this.formattingFn = this.options.formattingFn ? this.options.formattingFn : this.formatNumber),
                (this.easingFn = this.options.easingFn ? this.options.easingFn : this.easeOutExpo),
                (this.startVal = this.validateValue(this.options.startVal)),
                (this.frameVal = this.startVal),
                (this.endVal = this.validateValue(i)),
                (this.options.decimalPlaces = Math.max(this.options.decimalPlaces)),
                (this.decimalMult = Math.pow(10, this.options.decimalPlaces)),
                this.resetDuration(),
                (this.options.separator = String(this.options.separator)),
                (this.useEasing = this.options.useEasing),
                '' === this.options.separator && (this.options.useGrouping = !1),
                (this.el = 'string' == typeof t ? document.getElementById(t) : t),
                this.el ? this.printValue(this.startVal) : (this.error = '[CountUp] target is null or undefined');
        }
        return (
            (t.prototype.determineDirectionAndSmartEasing = function () {
                var t = this.finalEndVal ? this.finalEndVal : this.endVal;
                this.countDown = this.startVal > t;
                var i = t - this.startVal;
                if (Math.abs(i) > this.options.smartEasingThreshold) {
                    this.finalEndVal = t;
                    var a = this.countDown ? 1 : -1;
                    (this.endVal = t + a * this.options.smartEasingAmount), (this.duration = this.duration / 2);
                } else (this.endVal = t), (this.finalEndVal = null);
                this.finalEndVal ? (this.useEasing = !1) : (this.useEasing = this.options.useEasing);
            }),
            (t.prototype.start = function (t) {
                this.error ||
                    ((this.callback = t),
                    this.duration > 0
                        ? (this.determineDirectionAndSmartEasing(),
                          (this.paused = !1),
                          (this.rAF = requestAnimationFrame(this.count)))
                        : this.printValue(this.endVal));
            }),
            (t.prototype.pauseResume = function () {
                this.paused
                    ? ((this.startTime = null),
                      (this.duration = this.remaining),
                      (this.startVal = this.frameVal),
                      this.determineDirectionAndSmartEasing(),
                      (this.rAF = requestAnimationFrame(this.count)))
                    : cancelAnimationFrame(this.rAF),
                    (this.paused = !this.paused);
            }),
            (t.prototype.reset = function () {
                cancelAnimationFrame(this.rAF),
                    (this.paused = !0),
                    this.resetDuration(),
                    (this.startVal = this.validateValue(this.options.startVal)),
                    (this.frameVal = this.startVal),
                    this.printValue(this.startVal);
            }),
            (t.prototype.update = function (t) {
                cancelAnimationFrame(this.rAF),
                    (this.startTime = null),
                    (this.endVal = this.validateValue(t)),
                    this.endVal !== this.frameVal &&
                        ((this.startVal = this.frameVal),
                        this.finalEndVal || this.resetDuration(),
                        this.determineDirectionAndSmartEasing(),
                        (this.rAF = requestAnimationFrame(this.count)));
            }),
            (t.prototype.printValue = function (t) {
                var i = this.formattingFn(t);
                'INPUT' === this.el.tagName
                    ? (this.el.value = i)
                    : 'text' === this.el.tagName || 'tspan' === this.el.tagName
                    ? (this.el.textContent = i)
                    : (this.el.innerHTML = i);
            }),
            (t.prototype.ensureNumber = function (t) {
                return 'number' == typeof t && !isNaN(t);
            }),
            (t.prototype.validateValue = function (t) {
                var i = Number(t);
                return this.ensureNumber(i) ? i : ((this.error = '[CountUp] invalid start or end value: ' + t), null);
            }),
            (t.prototype.resetDuration = function () {
                (this.startTime = null),
                    (this.duration = 1e3 * Number(this.options.duration)),
                    (this.remaining = this.duration);
            }),
            t
        );
    })();
