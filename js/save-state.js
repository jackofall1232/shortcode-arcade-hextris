(function($) {
	// Safe localStorage wrappers
	function safeGetItem(key) {
		try {
			return localStorage.getItem(key);
		} catch (e) {
			return null;
		}
	}

	function safeSetItem(key, value) {
		try {
			localStorage.setItem(key, value);
		} catch (e) {
			// Storage not available
		}
	}

	window.exportSaveState = function() {
		var state = {};

		if(gameState == 1 || gameState == -1 || (gameState === 0 && safeGetItem('saveState') !== undefined && safeGetItem('saveState') !== null)) {
			state = {
				hex: $.extend(true, {}, MainHex),
				blocks: $.extend(true, [], blocks),
				score: score,
				wavegen: waveone,
				gdx: gdx,
				gdy: gdy,
				comboTime:settings.comboTime
			};

			state.hex.blocks.map(function(a){
				for (var i = 0; i < a.length; i++) {
					a[i] = $.extend(true, {}, a[i]);
				}

				a.map(descaleBlock);
			});

			for (var i = 0; i < state.blocks.length; i++) {
				state.blocks[i] = $.extend(true, {}, state.blocks[i]);
			}

			state.blocks.map(descaleBlock);
		}

		safeSetItem('highscores', JSON.stringify(highscores));

		return JSONfn.stringify(state);
	};

	window.descaleBlock = function(b) {
		b.distFromHex /= settings.scale;
	};

	window.writeHighScores = function() {
		highscores.sort(function(a,b){
			a = parseInt(a, 10);
			b = parseInt(b, 10);
			if (a < b) {
				return 1;
			} else if (a > b) {
				return -1;
			} else {
				return 0;
			}
		});
		highscores = highscores.slice(0,3);
		safeSetItem("highscores", JSON.stringify(highscores));
	};

	window.clearSaveState = function() {
		safeSetItem("saveState", "{}");
	};

	window.isStateSaved = function() {
		var saved = safeGetItem("saveState");
		return saved != "{}" && saved != undefined && saved != null;
	};

})(jQuery);
