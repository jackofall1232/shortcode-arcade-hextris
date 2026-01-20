/**
 * Hextris Game - WordPress Edition
 * A namespaced, self-contained version of Hextris for WordPress
 *
 * Original game by Garrett Finucane, Logan Engstrom, Noah Moroze, Gabriel Alsop
 * WordPress adaptation maintains all original gameplay
 */

(function(global) {
    'use strict';

    // Main game constructor
    function HextrisGame(containerId, options) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);

        if (!this.container) {
            console.error('Hextris: Container not found:', containerId);
            return;
        }

        // Default options
        this.options = Object.assign({
            width: '100%',
            height: '500px',
            difficulty: 'normal',
            showLeaderboard: true,
            colorScheme: 'default',
            onGameOver: null,
            onScoreUpdate: null
        }, options || {});

        // Game state
        this.gameState = -1; // -1: not started, 0: playing, 1: paused, 2: game over
        this.score = 0;
        this.highscores = [0, 0, 0];
        this.blocks = [];
        this.history = {};
        this.comboTime = 240;

        // Initialize
        this.init();
    }

    HextrisGame.prototype.init = function() {
        this.createCanvas();
        this.loadSettings();
        this.initializeGame();
        this.bindEvents();
        this.createUI();
        this.render();
    };

    HextrisGame.prototype.createCanvas = function() {
        var canvas = document.createElement('canvas');
        canvas.className = 'hextris-canvas';
        canvas.id = this.containerId + '-canvas';
        this.container.appendChild(canvas);

        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.trueCanvas = { width: 0, height: 0 };

        this.resizeCanvas();
    };

    HextrisGame.prototype.resizeCanvas = function() {
        var rect = this.container.getBoundingClientRect();
        var dpr = window.devicePixelRatio || 1;

        this.canvas.width = rect.width * dpr;
        this.canvas.height = rect.height * dpr;
        this.canvas.style.width = rect.width + 'px';
        this.canvas.style.height = rect.height + 'px';

        this.ctx.scale(dpr, dpr);

        this.trueCanvas.width = rect.width;
        this.trueCanvas.height = rect.height;

        // Update settings based on canvas size
        this.settings.scale = Math.min(rect.width, rect.height) / 400;
        this.settings.hexWidth = Math.min(rect.width, rect.height) / 5;
        this.settings.blockHeight = this.settings.hexWidth / 4;
    };

    HextrisGame.prototype.loadSettings = function() {
        // Load highscores from localStorage
        try {
            var stored = localStorage.getItem('hextris_highscores_' + this.containerId);
            if (stored) {
                this.highscores = JSON.parse(stored);
            }
        } catch (e) {
            console.warn('Could not load highscores');
        }

        // Color schemes
        var colorSchemes = {
            default: ['#e74c3c', '#f1c40f', '#3498db', '#2ecc71'],
            neon: ['#ff006e', '#fb5607', '#ffbe0b', '#8338ec'],
            pastel: ['#ffadad', '#ffd6a5', '#fdffb6', '#caffbf'],
            monochrome: ['#2c3e50', '#34495e', '#7f8c8d', '#95a5a6'],
            sunset: ['#ff6b6b', '#feca57', '#48dbfb', '#ff9ff3']
        };

        var scheme = this.options.colorScheme;
        var colors = colorSchemes[scheme] || colorSchemes.default;

        // Difficulty settings
        var difficultyMods = {
            easy: { speed: 0.7, creation: 0.7 },
            normal: { speed: 1, creation: 1 },
            hard: { speed: 1.4, creation: 1.3 }
        };
        var diff = difficultyMods[this.options.difficulty] || difficultyMods.normal;

        this.settings = {
            scale: 1,
            hexWidth: 80,
            blockHeight: 20,
            baseBlockSpeed: 1.5,
            comboTime: 240,
            colors: colors,
            speedModifier: diff.speed,
            creationSpeedModifier: diff.creation,
            platform: this.isMobile() ? 'mobile' : 'desktop'
        };

        this.colors = colors;
    };

    HextrisGame.prototype.isMobile = function() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    };

    HextrisGame.prototype.initializeGame = function() {
        // Create main hexagon
        this.MainHex = new this.Hex(this.settings.hexWidth);

        // Create wave generator
        this.waveGen = new this.WaveGen(this.MainHex);

        // Reset state
        this.blocks = [];
        this.score = 0;
        this.gameState = -1;
        this.gdx = 0;
        this.gdy = 0;
        this.lastTime = 0;
    };

    // Hex class
    HextrisGame.prototype.Hex = function(sideLength) {
        var game = this;

        this.playThrough = 0;
        this.fillColor = [44, 62, 80];
        this.tempColor = [44, 62, 80];
        this.angularVelocity = 0;
        this.position = 0;
        this.dy = 0;
        this.dt = 1;
        this.sides = 6;
        this.blocks = [];
        this.angle = 30;
        this.targetAngle = 30;
        this.shakes = [];
        this.sideLength = sideLength;
        this.ct = 0;
        this.lastCombo = this.ct - 240;
        this.lastColorScored = '#000';
        this.comboTime = 1;
        this.texts = [];
        this.lastRotate = Date.now();

        for (var i = 0; i < this.sides; i++) {
            this.blocks.push([]);
        }

        return this;
    };

    // Block class
    HextrisGame.prototype.Block = function(lane, color, iter) {
        this.lane = lane;
        this.color = color;
        this.iter = iter;
        this.settled = 0;
        this.targetAngle = 90 - lane * 60;
        this.angle = this.targetAngle;
        this.fallingLane = lane;
        this.height = 0;
        this.tint = 1;
        this.distFromHex = 0;
        this.checked = 0;
        this.attachedLane = -1;

        return this;
    };

    // Wave Generator
    HextrisGame.prototype.WaveGen = function(hex) {
        this.lastGen = 0;
        this.nextGen = 2700;
        this.ct = 0;
        this.hex = hex;
        this.difficulty = 1;
        this.dt = 0;

        return this;
    };

    HextrisGame.prototype.createUI = function() {
        var self = this;

        // Create overlay
        this.overlay = document.createElement('div');
        this.overlay.className = 'hextris-overlay';
        this.container.appendChild(this.overlay);

        // Create start screen
        this.startScreen = document.createElement('div');
        this.startScreen.className = 'hextris-start-screen';
        this.startScreen.innerHTML =
            '<div class="hextris-title">HEXTRIS</div>' +
            '<div class="hextris-subtitle">Click or tap to start</div>' +
            '<div class="hextris-instructions">' +
            (this.isMobile() ? 'Swipe to rotate' : 'Use arrow keys to rotate') +
            '</div>';
        this.container.appendChild(this.startScreen);

        // Create pause button
        this.pauseBtn = document.createElement('button');
        this.pauseBtn.className = 'hextris-btn hextris-pause-btn';
        this.pauseBtn.innerHTML = '❚❚';
        this.pauseBtn.style.display = 'none';
        this.pauseBtn.onclick = function() { self.togglePause(); };
        this.container.appendChild(this.pauseBtn);

        // Create restart button
        this.restartBtn = document.createElement('button');
        this.restartBtn.className = 'hextris-btn hextris-restart-btn';
        this.restartBtn.innerHTML = '↺';
        this.restartBtn.style.display = 'none';
        this.restartBtn.onclick = function() { self.restart(); };
        this.container.appendChild(this.restartBtn);

        // Create score display
        this.scoreDisplay = document.createElement('div');
        this.scoreDisplay.className = 'hextris-score-display';
        this.scoreDisplay.innerHTML = '<span class="hextris-score-value">0</span>';
        this.scoreDisplay.style.display = 'none';
        this.container.appendChild(this.scoreDisplay);

        // Create game over screen
        this.gameOverScreen = document.createElement('div');
        this.gameOverScreen.className = 'hextris-gameover-screen';
        this.gameOverScreen.style.display = 'none';
        this.container.appendChild(this.gameOverScreen);
    };

    HextrisGame.prototype.bindEvents = function() {
        var self = this;

        // Keyboard controls
        document.addEventListener('keydown', function(e) {
            if (!self.isActiveGame()) return;

            if (e.key === 'ArrowLeft' || e.key === 'a' || e.key === 'A') {
                self.rotate(-1);
                e.preventDefault();
            } else if (e.key === 'ArrowRight' || e.key === 'd' || e.key === 'D') {
                self.rotate(1);
                e.preventDefault();
            } else if (e.key === 'p' || e.key === 'P' || e.key === 'Escape') {
                self.togglePause();
                e.preventDefault();
            }
        });

        // Touch/swipe controls
        if (typeof Hammer !== 'undefined') {
            var hammer = new Hammer(this.canvas);
            hammer.get('swipe').set({ direction: Hammer.DIRECTION_HORIZONTAL });

            hammer.on('swipeleft', function() {
                if (self.isActiveGame()) self.rotate(-1);
            });

            hammer.on('swiperight', function() {
                if (self.isActiveGame()) self.rotate(1);
            });

            hammer.on('tap', function() {
                if (self.gameState === -1) {
                    self.startGame();
                }
            });
        }

        // Click to start
        this.startScreen.addEventListener('click', function() {
            self.startGame();
        });

        // Window resize
        window.addEventListener('resize', function() {
            self.resizeCanvas();
        });
    };

    HextrisGame.prototype.isActiveGame = function() {
        var rect = this.container.getBoundingClientRect();
        return rect.top < window.innerHeight && rect.bottom > 0;
    };

    HextrisGame.prototype.startGame = function() {
        this.gameState = 0;
        this.startScreen.style.display = 'none';
        this.pauseBtn.style.display = 'block';
        this.restartBtn.style.display = 'block';
        this.scoreDisplay.style.display = 'block';
        this.overlay.classList.remove('active');
    };

    HextrisGame.prototype.togglePause = function() {
        if (this.gameState === 0) {
            this.gameState = 1;
            this.pauseBtn.innerHTML = '▶';
            this.overlay.classList.add('active');
        } else if (this.gameState === 1) {
            this.gameState = 0;
            this.pauseBtn.innerHTML = '❚❚';
            this.overlay.classList.remove('active');
        }
    };

    HextrisGame.prototype.rotate = function(direction) {
        if (this.gameState !== 0) return;

        var now = Date.now();
        if (now - this.MainHex.lastRotate < 75 && !this.isMobile()) return;

        this.MainHex.position += direction;
        while (this.MainHex.position < 0) this.MainHex.position += 6;
        this.MainHex.position = this.MainHex.position % 6;

        this.MainHex.targetAngle -= direction * 60;

        for (var i = 0; i < this.MainHex.blocks.length; i++) {
            for (var j = 0; j < this.MainHex.blocks[i].length; j++) {
                this.MainHex.blocks[i][j].targetAngle -= direction * 60;
            }
        }

        this.MainHex.lastRotate = now;
    };

    HextrisGame.prototype.restart = function() {
        this.gameOverScreen.style.display = 'none';
        this.initializeGame();
        this.gameState = 0;
        this.pauseBtn.innerHTML = '❚❚';
        this.overlay.classList.remove('active');
    };

    HextrisGame.prototype.gameOver = function() {
        this.gameState = 2;

        // Update highscores
        this.highscores.push(this.score);
        this.highscores.sort(function(a, b) { return b - a; });
        this.highscores = this.highscores.slice(0, 3);

        // Save to localStorage
        try {
            localStorage.setItem('hextris_highscores_' + this.containerId, JSON.stringify(this.highscores));
        } catch (e) {}

        // Show game over screen
        this.gameOverScreen.innerHTML =
            '<div class="hextris-gameover-content">' +
            '<div class="hextris-gameover-title">GAME OVER</div>' +
            '<div class="hextris-final-score">' + this.score + '</div>' +
            '<div class="hextris-highscores-title">HIGH SCORES</div>' +
            '<div class="hextris-highscores-list">' +
            '<div>1. ' + this.highscores[0] + '</div>' +
            '<div>2. ' + this.highscores[1] + '</div>' +
            '<div>3. ' + this.highscores[2] + '</div>' +
            '</div>' +
            '<button class="hextris-play-again-btn">Play Again</button>' +
            '</div>';

        this.gameOverScreen.style.display = 'flex';

        var self = this;
        this.gameOverScreen.querySelector('.hextris-play-again-btn').onclick = function() {
            self.restart();
        };

        // Callback
        if (typeof this.options.onGameOver === 'function') {
            this.options.onGameOver({
                score: this.score,
                highscores: this.highscores
            });
        }
    };

    HextrisGame.prototype.addScore = function(points) {
        this.score += points;
        this.scoreDisplay.querySelector('.hextris-score-value').textContent = this.score;

        if (typeof this.options.onScoreUpdate === 'function') {
            this.options.onScoreUpdate(this.score);
        }
    };

    HextrisGame.prototype.addBlock = function(lane, color, speed) {
        var block = new this.Block(lane, color, speed);
        block.distFromHex = Math.max(this.trueCanvas.width, this.trueCanvas.height);
        block.height = this.settings.blockHeight;
        this.blocks.push(block);
    };

    HextrisGame.prototype.update = function(dt) {
        if (this.gameState !== 0) return;

        this.MainHex.ct++;
        this.MainHex.dt = dt / 16.667;

        // Update wave generator
        this.updateWaveGen();

        // Update hexagon rotation
        this.updateHexRotation();

        // Update blocks
        this.updateBlocks();

        // Check for matches
        this.checkMatches();

        // Check for game over
        this.checkGameOver();
    };

    HextrisGame.prototype.updateWaveGen = function() {
        var wg = this.waveGen;
        wg.dt = (this.settings.platform === 'mobile' ? 14 : 16.667) * this.MainHex.ct;

        if (wg.dt - wg.lastGen > wg.nextGen) {
            wg.ct++;
            wg.lastGen = wg.dt;

            var lane = Math.floor(Math.random() * 6);
            var color = this.colors[Math.floor(Math.random() * this.colors.length)];
            var speed = 1.5 + (wg.difficulty / 15) * 3;

            this.addBlock(lane, color, speed * this.settings.speedModifier);

            // Gradually increase difficulty
            if (wg.nextGen > 600) {
                wg.nextGen -= 11 * (wg.nextGen / 1300) * this.settings.creationSpeedModifier;
            }

            if (wg.difficulty < 35) {
                wg.difficulty += 0.05 * this.settings.speedModifier;
            }
        }
    };

    HextrisGame.prototype.updateHexRotation = function() {
        var hex = this.MainHex;
        var angularVelocityConst = 0.15;

        if (hex.angle > hex.targetAngle) {
            hex.angularVelocity -= angularVelocityConst * hex.dt;
        } else if (hex.angle < hex.targetAngle) {
            hex.angularVelocity += angularVelocityConst * hex.dt;
        }

        if (Math.abs(hex.angle - hex.targetAngle + hex.angularVelocity) <= Math.abs(hex.angularVelocity)) {
            hex.angle = hex.targetAngle;
            hex.angularVelocity = 0;
        } else {
            hex.angle += hex.angularVelocity;
        }
    };

    HextrisGame.prototype.updateBlocks = function() {
        var hex = this.MainHex;
        var hexRadius = (hex.sideLength / 2) * Math.sqrt(3);

        for (var i = this.blocks.length - 1; i >= 0; i--) {
            var block = this.blocks[i];

            if (block.settled) continue;

            // Update block angle
            var angleDiff = block.targetAngle - block.angle;
            block.angle += angleDiff * 0.2;

            // Move block toward center
            block.distFromHex -= block.iter * hex.dt * this.settings.scale;

            // Check collision with hexagon
            var lane = (6 - block.fallingLane + hex.position) % 6;
            var stack = hex.blocks[lane];

            var collisionDist = hexRadius;
            if (stack.length > 0) {
                collisionDist = stack[stack.length - 1].distFromHex + stack[stack.length - 1].height;
            }

            if (block.distFromHex <= collisionDist) {
                block.distFromHex = collisionDist;
                block.settled = 1;
                block.attachedLane = lane;
                stack.push(block);
                this.blocks.splice(i, 1);
            }
        }
    };

    HextrisGame.prototype.checkMatches = function() {
        var hex = this.MainHex;
        var matched = false;

        for (var lane = 0; lane < 6; lane++) {
            var stack = hex.blocks[lane];
            if (stack.length < 3) continue;

            // Check for 3+ consecutive same colors
            var count = 1;
            var startIdx = 0;

            for (var i = 1; i < stack.length; i++) {
                if (stack[i].color === stack[i - 1].color) {
                    count++;
                } else {
                    if (count >= 3) {
                        this.removeBlocks(lane, startIdx, count);
                        matched = true;
                        break;
                    }
                    count = 1;
                    startIdx = i;
                }
            }

            if (count >= 3 && !matched) {
                this.removeBlocks(lane, startIdx, count);
                matched = true;
            }
        }

        return matched;
    };

    HextrisGame.prototype.removeBlocks = function(lane, startIdx, count) {
        var hex = this.MainHex;
        var stack = hex.blocks[lane];

        // Calculate points (more for combos)
        var points = count * 10;
        if (count > 3) points += (count - 3) * 15;

        this.addScore(points);

        // Remove blocks
        stack.splice(startIdx, count);

        // Update remaining block positions
        var hexRadius = (hex.sideLength / 2) * Math.sqrt(3);
        for (var i = 0; i < stack.length; i++) {
            stack[i].distFromHex = hexRadius + i * stack[i].height;
        }
    };

    HextrisGame.prototype.checkGameOver = function() {
        var hex = this.MainHex;
        var maxDist = Math.min(this.trueCanvas.width, this.trueCanvas.height) / 2 - 20;

        for (var lane = 0; lane < 6; lane++) {
            var stack = hex.blocks[lane];
            if (stack.length > 0) {
                var topBlock = stack[stack.length - 1];
                if (topBlock.distFromHex + topBlock.height > maxDist) {
                    this.gameOver();
                    return;
                }
            }
        }
    };

    HextrisGame.prototype.render = function() {
        var self = this;
        var lastTime = 0;

        function loop(timestamp) {
            var dt = timestamp - lastTime;
            lastTime = timestamp;

            if (dt > 100) dt = 16.667; // Cap delta time

            self.update(dt);
            self.draw();

            requestAnimationFrame(loop);
        }

        requestAnimationFrame(loop);
    };

    HextrisGame.prototype.draw = function() {
        var ctx = this.ctx;
        var w = this.trueCanvas.width;
        var h = this.trueCanvas.height;

        // Clear canvas
        ctx.fillStyle = '#2c3e50';
        ctx.fillRect(0, 0, w, h);

        // Draw hexagon
        this.drawHexagon();

        // Draw settled blocks
        this.drawSettledBlocks();

        // Draw falling blocks
        this.drawFallingBlocks();
    };

    HextrisGame.prototype.drawHexagon = function() {
        var ctx = this.ctx;
        var hex = this.MainHex;
        var cx = this.trueCanvas.width / 2;
        var cy = this.trueCanvas.height / 2;

        ctx.save();
        ctx.translate(cx, cy);
        ctx.rotate(hex.angle * Math.PI / 180);

        ctx.beginPath();
        for (var i = 0; i < 6; i++) {
            var angle = (i * 60 - 30) * Math.PI / 180;
            var x = Math.cos(angle) * hex.sideLength;
            var y = Math.sin(angle) * hex.sideLength;

            if (i === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        }
        ctx.closePath();

        ctx.fillStyle = 'rgb(' + hex.fillColor.join(',') + ')';
        ctx.fill();

        ctx.restore();
    };

    HextrisGame.prototype.drawSettledBlocks = function() {
        var ctx = this.ctx;
        var hex = this.MainHex;
        var cx = this.trueCanvas.width / 2;
        var cy = this.trueCanvas.height / 2;

        for (var lane = 0; lane < 6; lane++) {
            var stack = hex.blocks[lane];

            for (var i = 0; i < stack.length; i++) {
                var block = stack[i];
                this.drawBlock(ctx, cx, cy, block, hex.angle);
            }
        }
    };

    HextrisGame.prototype.drawFallingBlocks = function() {
        var ctx = this.ctx;
        var cx = this.trueCanvas.width / 2;
        var cy = this.trueCanvas.height / 2;

        for (var i = 0; i < this.blocks.length; i++) {
            var block = this.blocks[i];
            this.drawBlock(ctx, cx, cy, block, block.angle);
        }
    };

    HextrisGame.prototype.drawBlock = function(ctx, cx, cy, block, hexAngle) {
        ctx.save();
        ctx.translate(cx, cy);
        ctx.rotate(hexAngle * Math.PI / 180);

        var laneAngle = (block.settled ? block.attachedLane : block.fallingLane) * 60;
        ctx.rotate(laneAngle * Math.PI / 180);

        // Draw trapezoid block
        var innerRadius = block.distFromHex;
        var outerRadius = block.distFromHex + block.height;
        var angleWidth = 28 * Math.PI / 180; // Slightly less than 30 for gap

        ctx.beginPath();
        ctx.moveTo(
            innerRadius * Math.cos(-angleWidth),
            innerRadius * Math.sin(-angleWidth)
        );
        ctx.lineTo(
            outerRadius * Math.cos(-angleWidth),
            outerRadius * Math.sin(-angleWidth)
        );
        ctx.lineTo(
            outerRadius * Math.cos(angleWidth),
            outerRadius * Math.sin(angleWidth)
        );
        ctx.lineTo(
            innerRadius * Math.cos(angleWidth),
            innerRadius * Math.sin(angleWidth)
        );
        ctx.closePath();

        ctx.fillStyle = block.color;
        ctx.fill();

        ctx.restore();
    };

    // Expose to global scope
    global.HextrisGame = HextrisGame;

})(window);
