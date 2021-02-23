// * Stopwatch class {{{
Stopwatch = function(listener, resolution) {
	this.startTime = 0;
	this.stopTime = 0;
	this.totalElapsed = 0; // * elapsed number of ms in total
	this.started = false;
	this.listener = (listener !== undefined ? listener : null); // * function to receive onTick events
	this.tickResolution = (resolution !== undefined ? resolution : 500); // * how long between each tick in milliseconds
	this.tickInterval = null;
	
	// * pretty static vars
	this.onehour = 1000 * 60 * 60;
	this.onemin  = 1000 * 60;
	this.onesec  = 1000;
};
Stopwatch.prototype.start = function() {
	var delegate = function(that, method) { return function() { return method.call(that); }; };
	if(!this.started) {
		this.startTime = new Date().getTime();
		this.stopTime = 0;
		this.started = true;
		this.tickInterval = setInterval(delegate(this, this.onTick), this.tickResolution);
	}
};
Stopwatch.prototype.stop = function() {
	if(this.started) {
		this.stopTime = new Date().getTime();
		this.started = false;
		var elapsed = this.stopTime - this.startTime;
		this.totalElapsed += elapsed;
		if(this.tickInterval !== null)
			clearInterval(this.tickInterval);
	}
	return this.getElapsed();
};
Stopwatch.prototype.reset = function() {
	this.totalElapsed = 0;
	// * if watch is running, reset it to current time
	this.startTime = new Date().getTime();
	this.stopTime = this.startTime;
};
Stopwatch.prototype.restart = function() {
	this.stop();
	this.reset();
	this.start();
};
Stopwatch.prototype.getElapsed = function() {
	// * if watch is stopped, use that date, else use now
	var elapsed = 0;
	if(this.started)
		elapsed = new Date().getTime() - this.startTime;
	elapsed += this.totalElapsed;
	
	var hours = parseInt(elapsed / this.onehour);
	elapsed %= this.onehour;
	var mins = parseInt(elapsed / this.onemin);
	elapsed %= this.onemin;
	var secs = parseInt(elapsed / this.onesec);
	var ms = elapsed % this.onesec;
	
	return {
		hours: hours,
		minutes: mins,
		seconds: secs,
		milliseconds: ms
	};
};
Stopwatch.prototype.setElapsed = function(hours, mins, secs) {
	this.reset();
	this.totalElapsed = 0;
	this.totalElapsed += hours * this.onehour;
	this.totalElapsed += mins  * this.onemin;
	this.totalElapsed += secs  * this.onesec;
	this.totalElapsed = Math.max(this.totalElapsed, 0); // * No negative numbers
};
Stopwatch.prototype.toString = function() {
	var zpad = function(no, digits) {
		no = no.toString();
		while(no.length < digits)
			no = '0' + no;
		return no;
	};
	var e = this.getElapsed();
	return zpad(e.hours,2) + ":" + zpad(e.minutes,2) + ":" + zpad(e.seconds,2);
};
Stopwatch.prototype.setListener = function(listener) {
	this.listener = listener;
};
// * triggered every <resolution> ms
Stopwatch.prototype.onTick = function() {
	if(this.listener !== null) {
		this.listener(this);
	}
};
// }}}

//ostatní funkce pro vyhodnocení závodu
/**
 * Funkce dostane řetězec ve formátu HH:MM:SS a převede ho na sekundy
 * @param {type} str vstupní řetězec HH:MMS:SS
 * @returns {Number} počet sekund
 */
function TimeToSec(str) {
        var pole = str.split(":");
        var vysl = pole[2]*1 + 60*pole[1] + 3600*pole[0];
        return vysl;
}
		
/**
 * Funkce převede sekundy do formátu HH:MM:SS
 * @param {type} s počet sekund
 * @returns {String} čas v požadovaném formátu
 */
function SecToTime(s) {
        //výpočty
        var sec = s%60;
        sec = sec.toFixed(3);
        s = s.toFixed(3);
        s -= sec;
        s /= 60;
        s = s.toFixed(3);
        var min = s%60;
        s -= min;
        s /= 60;

        s = s.toFixed(0);
        var hour =s;

        //přidání nul před jednociferná čísla
        if (sec.length <= 5)
                sec = "0" + sec;
        if (min < 10)
                min = "0" + min;
        if (hour < 10)
                hour = "0" + hour;

        //spojení jendotlivých části do jednoho stringu
        var vysl = hour + ":" + min + ":" + sec;

        return vysl;
}

/**
 * Funkce spočítá aktuální čas na vybraném řádku v závodu jednotlivců
 * @param {type} i id řádku
 * @returns {undefined}
 */
function evalRow(i) {
    //získání vteřin z jednotlivých inputů
	var start = TimeToSec($("[name='start_" + i + "']").val());
	var cil = TimeToSec($("[name='cil_" + i + "']").val());

	var trmin = $("[name='trmin_" + i + "']").val()*60;
	var stopcas = $("[name='stopcas_" + i + "']").val()*60;
	stopcas.toPrecision(3);
	//samotny vypocet
	var vysledek = SecToTime(cil-start+trmin-stopcas);
	
	//vyplnění výsledku do inputu vysledek
	$("input[name='vysledny_" + i + "']").val(vysledek);
}

/**
 * Zavolá EvalRow na všechny řádky závodu jednotlivců
 * @param {type} max počet řádků
 * @returns {undefined}
 */
function evalAll(rows) {
	//spočítáme všechny řády
	$.each(rows, function(i, val) {
		if ($("input[name='vybehl_" + val + "']").is(':checked'))
			evalRow(val);
	});
	$( "#race_form" ).submit();
}