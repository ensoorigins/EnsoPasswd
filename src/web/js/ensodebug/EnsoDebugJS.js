
EnsoDebug.prototype.ENSODEBUG_PRODUCTION_STR = 'production';
EnsoDebug.prototype.ENSODEBUG_DEVEL_STR = 'devel';

EnsoDebug.prototype.state = null;

function EnsoDebug() {
	this.state = this.ENSODEBUG_PRODUCTION_STR;
};

EnsoDebug.prototype.isDevel = function(){
	return (this.state==this.ENSODEBUG_DEVEL_STR)?1:0;
};

EnsoDebug.prototype.setDevel = function(){
	this.state = this.ENSODEBUG_DEVEL_STR;
};

EnsoDebug.prototype.setProduction=function(){
	this.state = this.ENSODEBUG_PRODUCTION_STR;
}

EnsoDebug.prototype.debug = function(facility, message){
	if (!this.isDevel())
		return;
	console.log('['+facility+'] '+message);
};

EnsoDebug.prototype.quickDebug = function(message){
	this.debug('quickDebug',message);
};