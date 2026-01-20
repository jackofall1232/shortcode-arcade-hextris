(function($) {
	// Helper to get image URL
	function getImageUrl(filename) {
		if (window.sacgaHextris && window.sacgaHextris.imagesUrl) {
			return window.sacgaHextris.imagesUrl + filename;
		}
		return './images/' + filename;
	}

	// Safe localStorage wrapper
	function safeGetItem(key) {
		try {
			return localStorage.getItem(key);
		} catch (e) {
			return null;
		}
	}

	window.scaleCanvas = function() {
		var $canvas = $('#canvas');
		var $container = $canvas.closest('.sacga-hextris');
		var containerWidth = $container.length ? $container.width() : 0;
		var containerHeight = $container.length ? $container.height() : 0;

		if (containerWidth > 0 && containerHeight > 0) {
			canvas.width = containerWidth;
			canvas.height = containerHeight;
		} else {
			canvas.width = $(window).width() || 800;
			canvas.height = $(window).height() || 600;
		}

		if (canvas.height > canvas.width) {
			settings.scale = (canvas.width / 800) * settings.baseScale;
		} else {
			settings.scale = (canvas.height / 800) * settings.baseScale;
		}

		if (settings.scale <= 0) {
			settings.scale = settings.baseScale;
		}

		trueCanvas = {
			width: canvas.width,
			height: canvas.height
		};

		if (window.devicePixelRatio) {
			var cw = $canvas.attr('width');
			var ch = $canvas.attr('height');

			$canvas.attr('width', cw * window.devicePixelRatio);
			$canvas.attr('height', ch * window.devicePixelRatio);
			$canvas.css('width', cw);
			$canvas.css('height', ch);

			trueCanvas = {
				width: cw,
				height: ch
			};

			ctx.setTransform(1, 0, 0, 1, 0, 0);
			ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
		}
		setBottomContainer();
		set_score_pos();
	};

	window.setBottomContainer = function() {
		var buttonOffset = $("#buttonCont").offset().top;
		var playOffset = trueCanvas.height / 2 + 100 * settings.scale;
		var delta = buttonOffset - playOffset - 29;
		if (delta < 0) {
			$("#bottomContainer").css("margin-bottom", "-" + Math.abs(delta) + "px");
		}
	};

	window.set_score_pos = function() {
		$("#container").css('margin-top', '0');
		var middle_of_container = ($("#container").height()/2 + $("#container").offset().top);
		var top_of_bottom_container = $("#buttonCont").offset().top;
		var igt = $("#highScoreInGameText");
		var igt_bottom = igt.offset().top + igt[0].offsetHeight;
		var target_midpoint = (top_of_bottom_container + igt_bottom)/2;
		var diff = (target_midpoint-middle_of_container);
		$("#container").css("margin-top",diff + "px");
	};

	window.toggleDevTools = function() {
		$('#devtools').toggle();
	};

	window.resumeGame = function() {
		gameState = 1;
		hideUIElements();
		$('#pauseBtn').show();
		$('#restartBtn').hide();
		importing = 0;
		startTime = Date.now();
		setTimeout(function() {
			if ((gameState == 1 || gameState == 2) && !$('#helpScreen').is(':visible')) {
				$('#openSideBar').fadeOut(150, "linear");
			}
		}, 7000);

		checkVisualElements(0);
	};

	window.checkVisualElements = function(arg) {
		if (arg && $('#openSideBar').is(":visible")) $('#openSideBar').fadeOut(150, "linear");
		if (!$('#pauseBtn').is(':visible')) $('#pauseBtn').fadeIn(150, "linear");
		$('#fork-ribbon').fadeOut(150);
		if (!$('#restartBtn').is(':visible')) $('#restartBtn').fadeOut(150, "linear");
		if ($('#buttonCont').is(':visible')) $('#buttonCont').fadeOut(150, "linear");
	};

	window.hideUIElements = function() {
		$('#pauseBtn').hide();
		$('#restartBtn').hide();
		$('#startBtn').hide();
	};

	window.init = function(b) {
		if(settings.ending_block && b == 1){return;}
		if (b) {
			$("#pauseBtn").attr('src', getImageUrl('btn_pause.svg'));
			if ($('#helpScreen').is(":visible")) {
				$('#helpScreen').fadeOut(150, "linear");
			}

			setTimeout(function() {
				if (gameState == 1) {
					$('#openSideBar').fadeOut(150, "linear");
				}
				infobuttonfading = false;
			}, 7000);
			clearSaveState();
			checkVisualElements(1);
		}
		if (highscores.length === 0 ){
			$("#currentHighScore").text(0);
		}
		else {
			$("#currentHighScore").text(highscores[0]);
		}
		infobuttonfading = true;
		$("#pauseBtn").attr('src', getImageUrl('btn_pause.svg'));
		hideUIElements();
		var saveState = safeGetItem("saveState") || "{}";
		saveState = JSONfn.parse(saveState);
		document.getElementById("canvas").className = "";
		history = {};
		importedHistory = undefined;
		importing = 0;
		score = saveState.score || 0;
		prevScore = 0;
		spawnLane = 0;
		op = 0;
		tweetblock=false;
		scoreOpacity = 0;
		gameState = 1;
		$("#restartBtn").hide();
		$("#pauseBtn").show();
		if (saveState.hex !== undefined) gameState = 1;

		settings.blockHeight = settings.baseBlockHeight * settings.scale;
		settings.hexWidth = settings.baseHexWidth * settings.scale;
		MainHex = saveState.hex || new Hex(settings.hexWidth);
		if (saveState.hex) {
			MainHex.playThrough += 1;
		}
		MainHex.sideLength = settings.hexWidth;

		var i;
		var block;
		if (saveState.blocks) {
			saveState.blocks.map(function(o) {
				if (rgbToHex[o.color]) {
					o.color = rgbToHex[o.color];
				}
			});

			for (i = 0; i < saveState.blocks.length; i++) {
				block = saveState.blocks[i];
				blocks.push(block);
			}
		} else {
			blocks = [];
		}

		gdx = saveState.gdx || 0;
		gdy = saveState.gdy || 0;
		comboTime = saveState.comboTime || 0;

		for (i = 0; i < MainHex.blocks.length; i++) {
			for (var j = 0; j < MainHex.blocks[i].length; j++) {
				MainHex.blocks[i][j].height = settings.blockHeight;
				MainHex.blocks[i][j].settled = 0;
			}
		}

		MainHex.blocks.map(function(i) {
			i.map(function(o) {
				if (rgbToHex[o.color]) {
					o.color = rgbToHex[o.color];
				}
			});
		});

		MainHex.y = -100;

		startTime = Date.now();
		waveone = saveState.wavegen || new waveGen(MainHex);

		MainHex.texts = [];
		MainHex.delay = 15;
		hideText();
	};

	window.addNewBlock = function(blocklane, color, iter, distFromHex, settled) {
		iter *= settings.speedModifier;
		if (!history[MainHex.ct]) {
			history[MainHex.ct] = {};
		}

		history[MainHex.ct].block = {
			blocklane: blocklane,
			color: color,
			iter: iter
		};

		if (distFromHex) {
			history[MainHex.ct].distFromHex = distFromHex;
		}
		if (settled) {
			blockHist[MainHex.ct].settled = settled;
		}
		blocks.push(new Block(blocklane, color, iter, distFromHex, settled));
	};

	window.exportHistory = function() {
		$('#devtoolsText').html(JSON.stringify(history));
		toggleDevTools();
	};

	window.setStartScreen = function() {
		$('#startBtn').show();
		init();
		if (isStateSaved()) {
			importing = 0;
		} else {
			importing = 1;
		}

		$('#pauseBtn').hide();
		$('#restartBtn').hide();
		$('#startBtn').show();

		gameState = 0;
		requestAnimFrame(animLoop);
	};

	window.spd = 1;

	window.animLoop = function() {
		switch (gameState) {
		case 1:
			requestAnimFrame(animLoop);
			render();
			var now = Date.now();
			var dt = (now - lastTime)/16.666 * rush;
			if (spd > 1) {
				dt *= spd;
			}

			if(gameState == 1 ){
				if(!MainHex.delay) {
					update(dt);
				}
				else{
					MainHex.delay--;
				}
			}

			lastTime = now;

			if (checkGameOver() && !importing) {
				var saveState = safeGetItem("saveState") || "{}";
				saveState = JSONfn.parse(saveState);
				gameState = 2;

				setTimeout(function() {
					enableRestart();
				}, 150);

				if ($('#helpScreen').is(':visible')) {
					$('#helpScreen').fadeOut(150, "linear");
				}

				if ($('#pauseBtn').is(':visible')) $('#pauseBtn').fadeOut(150, "linear");
				if ($('#restartBtn').is(':visible')) $('#restartBtn').fadeOut(150, "linear");
				if ($('#openSideBar').is(':visible')) $('.openSideBar').fadeOut(150, "linear");

				canRestart = 0;
				clearSaveState();
			}
			break;

		case 0:
			requestAnimFrame(animLoop);
			render();
			break;

		case -1:
			requestAnimFrame(animLoop);
			render();
			break;

		case 2:
			var now = Date.now();
			var dt = (now - lastTime)/16.666 * rush;
			requestAnimFrame(animLoop);
			update(dt);
			render();
			lastTime = now;
			break;

		case 3:
			requestAnimFrame(animLoop);
			fadeOutObjectsOnScreen();
			render();
			break;

		case 4:
			setTimeout(function() {
				initialize(1);
			}, 1);
			render();
			return;

		default:
			initialize();
			setStartScreen();
			break;
		}

		if (!(gameState == 1 || gameState == 2)) {
			lastTime = Date.now();
		}
	};

	window.enableRestart = function() {
		canRestart = 1;
	};

	window.isInfringing = function(hex) {
		for (var i = 0; i < hex.sides; i++) {
			var subTotal = 0;
			for (var j = 0; j < hex.blocks[i].length; j++) {
				subTotal += hex.blocks[i][j].deleted;
			}

			if (hex.blocks[i].length - subTotal > settings.rows) {
				return true;
			}
		}
		return false;
	};

	window.checkGameOver = function() {
		for (var i = 0; i < MainHex.sides; i++) {
			if (isInfringing(MainHex)) {
				if (highscores.indexOf(score) == -1) {
					highscores.push(score);
				}
				writeHighScores();
				gameOverDisplay();
				return true;
			}
		}
		return false;
	};

	window.showHelp = function() {
		var backImg = getImageUrl('btn_back.svg');
		var helpImg = getImageUrl('btn_help.svg');

		if ($('#openSideBar').attr('src') == backImg) {
			$('#openSideBar').attr('src', helpImg);
			if (gameState != 0 && gameState != -1 && gameState != 2) {
				$('#fork-ribbon').fadeOut(150, 'linear');
			}
		} else {
			$('#openSideBar').attr('src', backImg);
			if (gameState == 0 && gameState == -1 && gameState == 2) {
				$('#fork-ribbon').fadeIn(150, 'linear');
			}
		}

		$("#inst_main_body").html("<div id = 'instructions_head'>HOW TO PLAY</div><p>The goal of Hextris is to stop blocks from leaving the inside of the outer gray hexagon.</p><p>" + (settings.platform != 'mobile' ? 'Press the right and left arrow keys' : 'Tap the left and right sides of the screen') + " to rotate the Hexagon." + (settings.platform != 'mobile' ? ' Press the down arrow to speed up the block falling': '') + " </p><p>Clear blocks and get points by making 3 or more blocks of the same color touch.</p><p>Time left before your combo streak disappears is indicated by <span style='color:#f1c40f;'>the</span> <span style='color:#e74c3c'>colored</span> <span style='color:#3498db'>lines</span> <span style='color:#2ecc71'>on</span> the outer hexagon</p> <hr> <p id = 'afterhr'></p> By <a href='http://loganengstrom.com' target='_blank'>Logan Engstrom</a> & <a href='http://github.com/garrettdreyfus' target='_blank'>Garrett Finucane</a><br>Find Hextris on <a href = 'https://itunes.apple.com/us/app/id903769553?mt=8' target='_blank'>iOS</a> & <a href ='https://play.google.com/store/apps/details?id=com.hextris.hextris' target='_blank'>Android</a><br>More @ the <a href ='http://hextris.github.io/' target='_blank'>Hextris Website</a>");
		if (gameState == 1) {
			pause();
		}

		var pauseImg = getImageUrl('btn_pause.svg');
		if($("#pauseBtn").attr('src') == pauseImg && gameState != 0 && !infobuttonfading) {
			return;
		}

		$("#openSideBar").fadeIn(150,"linear");
		$('#helpScreen').fadeToggle(150, "linear");
	};

})(jQuery);
