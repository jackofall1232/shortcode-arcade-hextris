/**
 * Hextris WordPress Bridge
 * Integrates the Hextris game with WordPress functionality
 */

(function(global) {
    'use strict';

    var HextrisWP = {
        instances: {},
        config: global.hextrisGlobal || {},

        init: function() {
            var containers = document.querySelectorAll('.hextris-game-container');

            containers.forEach(function(container) {
                var id = container.id;
                if (!id) return;

                var options = HextrisWP.getContainerOptions(container);
                HextrisWP.createGame(id, options);
            });
        },

        getContainerOptions: function(container) {
            return {
                width: container.dataset.width || '100%',
                height: container.dataset.height || '500px',
                difficulty: container.dataset.difficulty || 'normal',
                showLeaderboard: container.dataset.leaderboard !== 'false',
                colorScheme: container.dataset.colorScheme || 'default',
                onGameOver: function(data) {
                    HextrisWP.handleGameOver(container.id, data);
                },
                onScoreUpdate: function(score) {
                    HextrisWP.handleScoreUpdate(container.id, score);
                }
            };
        },

        createGame: function(containerId, options) {
            if (typeof HextrisGame === 'undefined') {
                console.error('HextrisGame not loaded');
                return null;
            }

            var game = new HextrisGame(containerId, options);
            this.instances[containerId] = game;
            return game;
        },

        handleGameOver: function(containerId, data) {
            // Submit score to WordPress if user is logged in
            if (this.config.isLoggedIn && this.config.restUrl) {
                this.submitScore(data.score, containerId);
            }

            // Trigger custom event
            var event = new CustomEvent('hextris:gameover', {
                detail: { containerId: containerId, data: data }
            });
            document.dispatchEvent(event);
        },

        handleScoreUpdate: function(containerId, score) {
            var event = new CustomEvent('hextris:scoreupdate', {
                detail: { containerId: containerId, score: score }
            });
            document.dispatchEvent(event);
        },

        submitScore: function(score, containerId) {
            var self = this;
            var container = document.getElementById(containerId);
            var difficulty = container ? container.dataset.difficulty || 'normal' : 'normal';

            fetch(this.config.restUrl + '/scores', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.config.restNonce
                },
                body: JSON.stringify({
                    score: score,
                    difficulty: difficulty,
                    nonce: this.config.nonce
                })
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    var event = new CustomEvent('hextris:scoresubmitted', {
                        detail: { containerId: containerId, rank: data.rank }
                    });
                    document.dispatchEvent(event);
                }
            })
            .catch(function(error) {
                if (self.config.debugMode) {
                    console.error('Score submission failed:', error);
                }
            });
        },

        getLeaderboard: function(options) {
            options = options || {};
            var params = new URLSearchParams({
                limit: options.limit || 10,
                offset: options.offset || 0,
                difficulty: options.difficulty || 'all',
                period: options.period || 'all'
            });

            return fetch(this.config.restUrl + '/scores?' + params.toString())
                .then(function(response) {
                    return response.json();
                });
        },

        getInstance: function(containerId) {
            return this.instances[containerId] || null;
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            HextrisWP.init();
        });
    } else {
        HextrisWP.init();
    }

    global.HextrisWP = HextrisWP;

})(window);
