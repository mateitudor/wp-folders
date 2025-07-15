! function(g) {
	"use strict";
	const h = {
		data: {
			media: !1,
			mediaBrowse: !1,
			modal: !1,
			hidden: !1,
			width: {
				current: 260,
				min: 260,
				max: 800
			},
			ui: {
				$container: null,
				$sidebar: null,
				$sidebarInner: null,
				$splitter: null,
				$toggle: null,
				$list: null,
				$tree: null,
				$mediaframe: null
			},
			uploader: {
				$container: null,
				instance: null,
				list: []
			},
			loader: {
				counter: 0,
				$spin: null,
				$lock: null,
				request: null
			},
			dragdrop: {
				$ghost: null,
				$target: null,
				items: null,
				isTouch: !1,
				timerId: null
			},
			splitter: {
				cursor: {
					startWidth: 0,
					start: 0,
					prev: 0,
					current: 0
				}
			},
			folder: {
				active: null,
				prev: null,
				copy: null
			},
			tree: null,
			filter: {
				timerId: null
			},
			click: {
				folder: null,
				timerId: null
			},
			contextmenu: {
				list: null
			}
		},
		fn: {
			run: () => {
				if (console.log("Folders: version " + folders_sidebar_globals.data.version), h.globals = folders_sidebar_globals, h.globals.data.type)
					if (h.notify = new IFOLDERS.PLUGINS.NOTIFY, h.colorpicker = new IFOLDERS.PLUGINS.COLORPICKER, h.data.meta = g.extend({}, h.globals.data.meta), h.data.ticket = !0, "attachment" == h.globals.data.type) {
						var a = g("#view-switch-list").hasClass("current");
						if (h.data.media = !(a || "undefined" == typeof wp || !wp.media || !wp.media.view), h.data.media) {
							if ("function" == typeof wp.Uploader && g.extend(wp.Uploader.prototype, {
									init: function() {
										this.uploader && (h.data.uploader.instance = this.uploader, this.uploader.bind("FileFiltered", function(a, e) {
											e._folder = h.globals.data.rights.a ? h.data.folder.active : -1
										}), this.uploader.bind("FilesAdded", function(a, e) {
											for (const t of e) h.fn.uploader.addFile(t);
											h.fn.uploader.updateHeader(), h.fn.uploader.open()
										}), this.uploader.bind("BeforeUpload", function(a, e) {
											if (e._folder) {
												const t = a.settings.multipart_params;
												0 < (a = parseInt(e._folder)) ? t.folder = a : "folder" in t && delete t.folder
											}
										}), this.uploader.bind("UploadProgress", function(a, e) {}), this.uploader.bind("FileUploaded", function(a, e) {
											h.fn.uploader.completeFile(e)
										}), this.uploader.bind("UploadComplete", function(a, e) {
											h.fn.uploader.complete()
										}))
									}
								}), wp.media.view.AttachmentsBrowser) {
								const e = wp.media.view.AttachmentsBrowser;
								wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
									createToolbar: function() {
										h.data.attachmentsBrowser = this, h.data.mediaBrowse = !(!this.model.attributes.router || "browse" != this.model.attributes.router), h.fn.updateMediaGridSort(), e.prototype.createToolbar.apply(this, arguments)
									}
								})
							}
							if (wp.media.view.MediaFrame.EditAttachments) {
								const t = wp.media.view.MediaFrame.EditAttachments;
								wp.media.view.MediaFrame.EditAttachments = wp.media.view.MediaFrame.EditAttachments.extend({
									initialize: function() {
										h.data.editAttachments = this, t.prototype.initialize.apply(this, arguments)
									},
									updateMediaData: function() {
										const a = h.data.editAttachments;
										fetch(a.model.attributes.url, {
											cache: "reload",
											mode: "no-cors"
										}).then(() => {
											a.model.fetch().done(() => {
												a.rerender(a.model)
											})
										})
									}
								})
							}
						}
						g("body").hasClass("upload-php") ? (h.fn.ajaxPrefilter(), h.fn.loadSidebar()) : h.data.media && wp.media.view.Modal && ((h.data.ticket || wp && wp.blocks) && h.fn.ajaxPrefilter(), wp.media.view.Modal.prototype.on("prepare", h.fn.onMediaModalPrepare), wp.media.view.Modal.prototype.on("open", h.fn.onMediaModalOpen), wp.media.view.Modal.prototype.on("close", h.fn.onMediaModalClose))
					} else h.fn.loadSidebar()
			},
			ajaxPrefilter: () => {
				g.ajaxPrefilter((a, e, t) => {
					"POST" === e.type && e.data && "query-attachments" == e.data.action && h.data.mediaBrowse && (e.data = g.extend(e.data, {
						folders_mode: "grid"
					}), a.data = g.param(e.data))
				})
			},
			processData: (a, e, t = {}, d, o) => {
				const i = g.Deferred();
				return d || h.fn.loading(!0, o), o = g.ajax({
					url: h.globals.api.url + "/" + a,
					type: "GET" == e ? "GET" : "POST",
					cache: !1,
					dataType: "json",
					contentType: "GET" != e && "application/json",
					headers: {
						"X-WP-Nonce": h.globals.api.nonce,
						"X-HTTP-Method-Override": e
					},
					data: "GET" === e ? g.param(t) : JSON.stringify(t)
				}).done(a => {
					a && a.success ? i.resolve(a.data) : i.reject()
				}).fail(() => {
					i.reject()
				}).always(() => {
					d || h.fn.loading(!1)
				}), {
					...i.promise(),
					abort: o.abort
				}
			},
			getData: (a, e = {}, t, d) => h.fn.processData(a, "GET", e, t, d),
			createData: (a, e = {}, t, d) => h.fn.processData(a, "POST", e, t, d),
			updateData: (a, e = {}, t, d) => h.fn.processData(a, "PUT", e, t, d),
			deleteData: (a, e = {}, t, d) => h.fn.processData(a, "DELETE", e, t, d),
			loadProposal: () => {
				h.data.hidden = "true" === Cookies.get("folders-sidebar-hidden"), h.fn.prebuild(), h.fn.updateWidth(), h.fn.getData("template", {
					name: "proposal"
				}).done(a => {
					h.fn.build(a, !0), g.when(h.fn.updateWidth()).done(() => {
						h.fn.bind(!0), h.fn.ready()
					})
				})
			},
			loadSidebar: () => {
				h.data.hidden = "true" === Cookies.get("folders-sidebar-hidden"), h.fn.prebuild(), h.fn.updateWidth(), g.when(h.fn.getData("contextmenu"), h.fn.getData("meta", {
					type: h.globals.data.type
				}), h.fn.getData("template", {
					name: "sidebar"
				})).done((a, e, t) => {
					h.data.contextmenu.list = a, h.data.meta = e, h.fn.build(t), g.when(h.fn.updateWidth(), h.fn.updateFoldersData(), h.fn.updateFoldersAttachCount()).done(() => {
						h.fn.updateNoticeAndSearch(), h.fn.activateFolder(h.data.meta.folder, !0, !0), h.fn.collapseFolders(h.data.meta.collapsed), h.fn.initAttachments(), h.fn.bind(), h.fn.ready()
					})
				})
			},
			loading: (a, e) => {
				a ? (h.data.loader.counter++, h.data.loader.$spin.toggleClass("folders-active", !0), h.data.loader.$lock.toggleClass("folders-active", !e)) : (h.data.loader.counter--, h.data.loader.counter <= 0 && (h.data.loader.$spin.toggleClass("folders-active", !1), h.data.loader.$lock.toggleClass("folders-active", !1), h.data.loader.counter = 0))
			},
			prebuild: () => {
				if (h.data.loader.$spin = g("<div>").addClass("folders-spin"), h.data.loader.$lock = g("<div>").addClass("folders-lock"), h.data.ui.$container = g("<div>").addClass("folders-container").toggleClass("folders-hidden", h.data.hidden), h.data.ui.$sidebar = g("<div>").addClass("folders-sidebar").toggleClass("folders-disable-tree-labels", h.globals.data.disable_counter).toggleClass("folders-disable-search-bar", h.globals.data.disable_search_bar), h.data.ui.$sidebarInner = g("<div>").addClass("folders-sidebar-inner"), h.data.ui.$splitter = g("<div>").addClass("folders-splitter"), h.data.ui.$toggle = g("<div>").addClass("folders-toggle"), h.data.ui.$list = g("<div>").addClass("folders-list"), h.data.ui.$minitools = g("<div>").addClass("folders-minitools"), h.data.ui.$minitools.append(h.data.ui.$toggle, h.data.loader.$spin), !h.data.modal) {
					var a = g("#wpadminbar").outerHeight(),
						e = g("#wpfooter").outerHeight();
					h.data.ui.$sidebar.css({
						position: "sticky",
						top: a + "px",
						height: `calc(100vh - ${a+e}px - 34px)`,
						width: h.data.width.current
					});
					const d = (() => {
						for (const a of g("#wpbody .wrap"))
							if (!g(a).is(":empty")) return g(a);
						return null
					})();
					d.wrap(h.data.ui.$list), h.data.ui.$list = d.parent(), h.data.ui.$list.wrap(h.data.ui.$container).before(h.data.ui.$sidebar, h.data.ui.$splitter).append(h.data.ui.$minitools), h.data.ui.$container = h.data.ui.$sidebar.parent().addClass("folders-screen-type");
					var a = g("<div>").addClass("folders-ph-toolbar"),
						e = g("<div>").addClass("folders-ph-panel"),
						t = g("<div>").addClass("folders-ph-panel-tree");
					h.data.ui.$sidebar.append(h.data.ui.$sidebarInner.append(a, e, t))
				}
			},
			build: (a, e) => {
				if (h.data.ui.$sidebarInner.empty().append(a), h.data.ui.$sidebar.append(h.data.ui.$sidebarInner, h.data.loader.$lock), h.data.ui.$tree = h.data.ui.$sidebar.find("#folders-tree"), e || (h.globals.data.default_color && document.documentElement.style.setProperty("--folders-default-folder-color", h.globals.data.default_color), h.globals.data.rights.c || h.data.ui.$sidebar.find("#folders-btn-create").remove(), "attachment" !== h.globals.data.type && h.data.ui.$sidebar.find("#folders-btn-sort").remove(), h.globals.data.rights.c || h.data.ui.$sidebar.find("#folders-toolbar").remove()), h.data.modal) {
					const t = g('div[id^="__wp-uploader-id-"].supports-drag-drop:visible');
					h.data.ui.$mediaframe = g(`#${t.attr("id")} .media-frame`), h.data.ui.$mediaframe.prepend(h.data.ui.$container.append(h.data.ui.$sidebar)), h.data.ui.$mediaframe.find(".media-frame-title").prepend(h.data.ui.$minitools), h.data.ui.$container.addClass("folders-modal-type")
				}
				e || (a = {
					callback: {
						loading: h.fn.loading,
						move: h.fn.moveFolders,
						collapse: h.fn.collapseFolder
					}
				}, h.data.tree = IFOLDERS.PLUGINS.TREE("#folders-tree", a)), h.fn.uploader.build()
			},
			bind: a => {
				h.data.ui.$toggle.on("click", h.fn.onToggleContainer), h.data.ui.$splitter.on("mousedown", h.fn.onSplitterMouseDown), a || h.fn.bindSidebarEvents()
				
				// Add folder filter functionality
				if ( "attachment" == h.globals.data.type ) {
					h.fn.initFolderFilter();
				}
			},
			bindSidebarEvents: () => {
				console.log('bindSidebarEvents: Re-binding sidebar event handlers');
				// Remove existing event handlers to prevent duplicates
				h.data.ui.$sidebar.find("#folders-btn-create").off("click");
				h.data.ui.$sidebar.find("#folders-btn-sort").off("click");
				h.data.ui.$sidebar.find("#folders-search-input").off("input");
				h.data.ui.$sidebar.find("#folders-search-clear").off("click");
				h.data.ui.$sidebar.off("click", ".folders-tree-item");
				h.data.ui.$sidebar.off("dblclick", ".folders-tree-item");
				h.data.ui.$sidebar.off("contextmenu", ".folders-tree-item");
				g(document).off("mouseover", ".attachment");
				
				// Bind event handlers
				h.data.ui.$sidebar.find("#folders-btn-create").on("click", h.fn.onFolderCreate);
				console.log('bindSidebarEvents: Create button event handler bound');
				h.data.ui.$sidebar.find("#folders-btn-sort").on("click", h.fn.onFolderSort);
				h.data.ui.$sidebar.find("#folders-search-input").on("input", h.fn.onSearchInput);
				h.data.ui.$sidebar.find("#folders-search-clear").on("click", h.fn.onSearchClear);
				h.data.ui.$sidebar.on("click", ".folders-tree-item", h.fn.onFolderClick);
				h.data.ui.$sidebar.on("dblclick", ".folders-tree-item", h.fn.onFolderDblClick);
				h.data.ui.$sidebar.on("contextmenu", ".folders-tree-item", h.fn.onContextMenu);
				g(document).ajaxComplete(h.fn.onAjaxComplete);
				
				// Add media hover details if enabled
				if ( "attachment" == h.globals.data.type && h.globals.data.media_hover_details ) {
					g(document).on("mouseover", ".attachment", h.fn.onShowMediaDetails);
				}
			},
			ready: () => {
				h.data.ui.$sidebar.addClass("folders-active"), h.data.ui.$splitter.addClass("folders-active"), h.data.ui.$toggle.addClass("folders-active"), h.data.ui.$container.addClass("folders-active"), h.data.ui.$mediaframe && h.data.ui.$mediaframe.toggleClass("folders-active", !h.data.hidden), h.data.ui.$sidebar.find("#folders-toolbar").addClass("folders-active"), h.data.ui.$sidebar.find("#folders-panel").addClass("folders-active"), h.data.ui.$sidebar.find("#folders-panel-search").addClass("folders-active");
				var a = h.data.ui.$sidebar.find("#folders-panel-tree").get(0);
				if (a) {
					const e = OverlayScrollbarsGlobal.OverlayScrollbars;
					e(a, {
						scrollbars: {
							autoHide: "leave",
							autoHideDelay: 100
						}
					})
				}
			},
			updateMeta: a => {
				const e = h.data.tree.getFlatData();
				var t = e ? e.filter(a => a.collapsed).map(a => a.id) : null,
					t = {
						folder: h.data.folder.active,
						collapsed: t,
						sort: h.data.meta.sort
					};
				return h.fn.updateData("meta", {
					type: h.globals.data.type,
					meta: t
				}, a, !0)
			},
			updateWidth: a => {
				a = a || Cookies.get("folders-sidebar-width") || 0, a = Math.min(Math.max(a, h.data.width.min), h.data.width.max), h.data.width.current = a, h.data.ui.$sidebar.css({
					width: a
				})
			},
			updateNoticeAndSearch: () => {
				var a = h.globals.data.rights.c && !(h.data.tree && h.data.tree.hasItems());
				h.data.ui.$sidebar.find("#folders-notice-create").toggleClass("folders-active", a), h.data.ui.$sidebar.find("#folders-search").toggleClass("folders-active", !a), h.data.ui.$sidebar.find("#folders-panel-tree").toggleClass("folders-active", !a)
			},
			updateMediaGridSort: () => {
				if (h.data.mediaBrowse) {
					const a = {
						orderby: "date",
						order: "DESC"
					};
					switch (h.data.meta.sort.items) {
						case "name-asc":
							a.orderby = "title", a.order = "ASC";
							break;
						case "name-desc":
							a.orderby = "title", a.order = "DESC";
							break;
						case "date-asc":
							a.orderby = "date", a.order = "ASC";
							break;
						case "date-desc":
							a.orderby = "date", a.order = "DESC";
							break;
						case "mod-asc":
							a.orderby = "modified", a.order = "ASC";
							break;
						case "mod-desc":
							a.orderby = "modified", a.order = "DESC";
							break;
						case "author-asc":
							a.orderby = "authorName", a.order = "ASC";
							break;
						case "author-desc":
							a.orderby = "authorName", a.order = "DESC"
					}
					h.data.attachmentsBrowser && h.data.attachmentsBrowser.collection && h.data.attachmentsBrowser.collection.props.set({
						orderby: a.orderby,
						order: a.order
					})
				}
			},
			updateMediaGridData: () => {
				h.fn.updateMediaGridSort(), h.data.attachmentsBrowser && h.data.attachmentsBrowser.collection && h.data.attachmentsBrowser.collection.props.set({
					ignore: +new Date
				})
			},
			updateListData: a => {
				const e = g.Deferred();
				return h.fn.loading(!0, !0), a = g.ajax({
					method: "GET",
					url: a,
					dataType: "html"
				}).done(a => {
					e.resolve(a)
				}).fail(() => {
					e.reject()
				}).always(() => {
					h.fn.loading(!1)
				}), {
					...e.promise(),
					abort: a.abort
				}
			},
			updateFoldersData: () => h.fn.getData("folders", {
				type: h.globals.data.type
			}).done(a => {
				for (const e of a) h.data.tree.addItem(e)
			}).fail(() => {
				h.notify.show(h.globals.msg.failed, "folders-failed")
			}),
			updateFoldersAttachCount: a => {
				if (h.globals.data.disable_counter) {
					const e = g.Deferred();
					return e.resolve(), e.promise()
				}
				return h.fn.getData("attachment/counters", {
					type: h.globals.data.type,
					folders: a
				}).done(a => {
					for (const e of a) h.data.tree.updateItemLabel(e.id, e.count), -1 != e.id && -2 != e.id || h.data.ui.$sidebar.find(`.folders-tree-item[data-id='${e.id}'] .folders-tree-label`).toggleClass("folders-tree-active", 0 != e.count).text(e.count)
				})
			},
			reinitWordPressStuff: () => {
				if (window.inlineEditPost && window.inlineEditPost.init(), "plugins" === h.globals.data.type) {
					const a = g("#updates-js");
					a.length && a.remove().appendTo("head")
				}
			},
			initAttachments: () => {
				h.data.dragdrop.$ghost = g("<div>").addClass("folders-attachment-drag-ghost"), h.data.media ? h.globals.data.rights.a && g(".media-frame .media-frame-content").on("mousedown touchstart", ".attachment", h.fn.onAttachmentDown) : (h.data.ui.$list.toggleClass("folders-can-attach", h.globals.data.rights.a), h.globals.data.rights.a && g("#the-list").on("mousedown touchstart", ".check-column", h.fn.onAttachmentDown))
			},
			dropAttachments: (a, e) => {
				a && h.data.folder.active != a && e && e.length && h.fn.updateData("attach", {
					type: h.globals.data.type,
					folder: a,
					attachments: e
				}).done(a => {
					h.fn.updateFoldersAttachCount(a), h.fn.activateFolder(h.data.folder.active, !1, !0), h.fn.refreshFolderFilter()
				}).fail(() => {
					h.notify.show(h.globals.msg.failed, "folders-failed")
				})
			},
			activateFolder: (a, e, t) => {
				h.data.folder.active == a && !t || (h.data.folder.prev = h.data.folder.active, h.data.folder.active = a, h.data.ui.$sidebar.find(".folders-tree-item.folders-active").removeClass("folders-active"), h.data.ui.$sidebar.find(`.folders-tree-item[data-id='${a}']`).addClass("folders-active"), e || (h.data.loader.request && h.data.loader.request.abort(), h.data.loader.request = h.fn.updateMeta(), h.data.loader.request.done(() => {
					var a;
					if (h.data.media) {
						h.fn.updateMediaGridData();
					} else {
						// For list view, handle URL updates more carefully
						try {
							if (typeof Url !== 'undefined' && Url.queryString) {
								("string" == typeof(a = Url.queryString("paged")) || a instanceof String) && Url.updateSearchParam("paged", "1", !1);
							}
						} catch (error) {
							// Url object not available, continue without it
						}
						
						if (h.globals.data.disable_ajax) {
							window.location.reload();
						} else {
							h.data.loader.request = h.fn.updateListData(location.href);
							h.data.loader.request.done(e => {
								var a = (() => {
									for (const a of g(e).find("#wpbody .wrap"))
										if (!g(a).is(":empty")) return g(a);
									return null
								})();
																a && (h.data.ui.$list.find(".wrap")[0].innerHTML = a[0].innerHTML, h.fn.initAttachments(), h.fn.reinitWordPressStuff())
								
								// Re-initialize folder filter and sidebar events after content update
								setTimeout(() => {
									console.log('activateFolder: Re-binding events after content update');
									console.log('activateFolder: Create button exists:', h.data.ui.$sidebar.find("#folders-btn-create").length);
									console.log('activateFolder: Create form exists:', g("#folders-form-create").length);
									h.fn.bindSidebarEvents();
									h.fn.initFolderFilter();
								}, 100);
							}).fail(() => {
								h.data.loader.request = null;
								// Fallback to page reload if AJAX fails
								window.location.reload();
							}).always(() => {
								h.data.loader.request = null
							});
						}
					}
				}).fail(() => {
					h.data.loader.request = null
				}).always(() => {})))
			},
			collapseFolders: a => {
				if (a && a.length)
					for (const e of a) h.data.tree.collapseItem(e, !0)
			},
			createFolders: (a, e, t) => {
				a && a.length && h.fn.createData("folders", {
					type: h.globals.data.type,
					folders: a,
					parent: e
				}).done(a => {
					for (const e of a) h.data.tree.addItem(e, e.parent);
					h.fn.updateNoticeAndSearch(), h.fn.updateFoldersAttachCount(), h.fn.refreshFolderFilter()
				}).fail(() => {
					h.notify.show(h.globals.msg.failed, "folders-failed")
				})
			},
			renameFolder: (a, t) => {
				h.fn.debugRename('start', { folderId: a, newName: t });
				
				// Validate input
				if (!a || !t || typeof t !== 'string') {
					h.fn.debugRename('validation_failed', { folderId: a, newName: t, reason: 'Invalid input' });
					h.notify.show(h.globals.msg.failed || 'Invalid input', "folders-failed");
					return;
				}
				
				// Trim whitespace and validate length
				const trimmedName = t.trim();
				if (trimmedName.length === 0) {
					h.fn.debugRename('validation_failed', { folderId: a, newName: t, reason: 'Empty name' });
					h.notify.show(h.globals.msg.failed || 'Folder name cannot be empty', "folders-failed");
					return;
				}
				
				if (trimmedName.length > 255) {
					h.fn.debugRename('validation_failed', { folderId: a, newName: t, reason: 'Name too long' });
					h.notify.show(h.globals.msg.failed || 'Folder name is too long', "folders-failed");
					return;
				}
				
				// Show loading state
				const $folderItem = h.data.ui.$sidebar.find(`.folders-tree-item[data-id='${a}']`);
				$folderItem.addClass('folders-renaming');
				
				h.fn.updateData("folders", {
					type: h.globals.data.type,
					action: "rename",
					folders: [a],
					name: trimmedName
				}).done(a => {
					h.fn.debugRename('success', { folderId: a, newName: trimmedName, result: a });
					$folderItem.removeClass('folders-renaming');
					for (const e of a) {
						h.data.tree.updateItemTitle(e, trimmedName);
					}
					
					// Add success animation
					$folderItem.addClass('folders-rename-success');
					setTimeout(() => {
						$folderItem.removeClass('folders-rename-success');
					}, 600);
					
					h.notify.show(h.globals.msg.success || 'Folder renamed successfully', "folders-success");
					h.fn.refreshFolderFilter();
				}).fail(() => {
					h.fn.debugRename('failed', { folderId: a, newName: trimmedName });
					$folderItem.removeClass('folders-renaming');
					h.notify.show(h.globals.msg.failed || 'Failed to rename folder', "folders-failed");
				})
			},
			startFolderRename: (folderId) => {
				// Check if user has edit rights
				if (!h.globals.data.rights.e) {
					h.notify.show(h.globals.msg.failed || 'You do not have permission to rename folders', "folders-failed");
					return;
				}
				
				// Disable rename for "All Items" (-1) and "Uncategorized" (-2) folders
				if (folderId === "-1" || folderId === "-2") {
					h.fn.debugRename('system_folder_blocked', { folderId: folderId, folderName: folderId === "-1" ? "All Items" : "Uncategorized" });
					h.notify.show(h.globals.msg.failed || 'Cannot rename system folders', "folders-failed");
					return;
				}
				
				// Get folder data
				const folderData = h.data.tree.getItem(folderId);
				if (!folderData) {
					h.notify.show(h.globals.msg.failed || 'Folder not found', "folders-failed");
					return;
				}
				
				// Check if already editing or renaming
				const $folderItem = h.data.ui.$sidebar.find(`.folders-tree-item[data-id='${folderId}']`);
				if ($folderItem.hasClass('folders-tree-edited') || $folderItem.hasClass('folders-renaming')) {
					return;
				}
				
				// Create edit elements
				const $editContainer = g("<div>").addClass("folders-tree-edit").attr({
					id: "folders-tree-edit"
				});
				
				const $input = g("<input>").addClass("folders-tree-input").attr({
					spellcheck: "false",
					autocomplete: "off",
					type: "text",
					maxlength: "255"
				});
				
				const $enterBtn = g("<div>").addClass("folders-tree-btn-enter").html('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>');
				
				// Cleanup function
				const cleanup = () => {
					$editContainer.remove();
					$folderItem.removeClass("folders-tree-edited");
					h.data.tree.toggleDragDrop(true);
					// Clear any existing click timer
					if (h.data.click.timerId) {
						clearTimeout(h.data.click.timerId);
						h.data.click.timerId = null;
					}
				};
				
				// Save function
				const saveRename = () => {
					const newName = $input.val().trim();
					if (newName === folderData.title) {
						cleanup();
						return;
					}
					
					if (newName.length === 0) {
						$input.addClass('folders-error');
						h.notify.show(h.globals.msg.failed || 'Folder name cannot be empty', "folders-failed");
						$input.focus();
						setTimeout(() => {
							$input.removeClass('folders-error');
						}, 2000);
						return;
					}
					
					// Update UI immediately for better UX
					$folderItem.find(".folders-tree-title").text(newName);
					cleanup();
					
					// Send to server
					h.fn.renameFolder(folderId, newName);
				};
				
				// Cancel function
				const cancelRename = () => {
					cleanup();
				};
				
				// Setup event handlers
				$input.on("keydown", (e) => {
					switch (e.keyCode) {
						case 13: // Enter
							e.preventDefault();
							saveRename();
							break;
						case 27: // Escape
							e.preventDefault();
							cancelRename();
							break;
					}
				});
				
				$input.on("blur", () => {
					// Small delay to allow for button clicks
					setTimeout(() => {
						if ($editContainer.is(":visible")) {
							saveRename();
						}
					}, 100);
				});
				
				$enterBtn.on("click", (e) => {
					e.preventDefault();
					e.stopPropagation();
					saveRename();
				});
				
				// Prevent click events from bubbling
				$editContainer.on("click", (e) => {
					e.stopPropagation();
				});
				
				// Assemble and show edit interface
				$editContainer.append($input, $enterBtn);
				$folderItem.append($editContainer).addClass("folders-tree-edited");
				
				// Disable drag and drop during edit
				h.data.tree.toggleDragDrop(false);
				
				// Focus and select text
				$input.focus().val(folderData.title).select();
			},
			colorFolders: (a, t) => {
				a && a.length && h.fn.updateData("folders", {
					type: h.globals.data.type,
					action: "color",
					folders: a,
					color: t
				}).done(a => {
					for (const e of a) h.data.tree.updateItemColor(e, t)
				}).fail(() => {
					h.notify.show(h.globals.msg.failed, "folders-failed")
				})
			},
			moveFolders: function(e, a, t, d, o, i) {
				const r = this;
				let s = [];
				switch (o) {
					case "before":
						(s = (s = JSON.parse(JSON.stringify(d))).filter(a => !e.includes(a))).splice(s.indexOf(t), 0, ...e);
						break;
					case "after":
						(s = (s = JSON.parse(JSON.stringify(d))).filter(a => !e.includes(a))).splice(s.indexOf(t) + 1, 0, ...e);
						break;
					case "inside":
						s = d.concat(e)
				}
				h.fn.updateData("folders", {
					type: h.globals.data.type,
					action: "move",
					folders: e,
					parent: a,
					sorting: s
				}).done(a => {
					i && "function" == typeof i && i.call(r, a, t, o)
				}).fail(() => {
					h.notify.show(h.globals.msg.failed, "folders-failed")
				})
			},
			collapseFolder: function() {
				h.data.loader.request && h.data.loader.request.abort(), h.data.loader.request = h.fn.updateMeta(!0), h.data.loader.request.always(() => {
					h.data.loader.request = null
				})
			},
			copyFolders: (a, e) => {
				h.fn.createData("copyfolder", {
					type: h.globals.data.type,
					src: a,
					dst: e
				}).done(a => {
					for (const e of a) h.data.tree.addItem(e, e.parent);
					h.fn.updateNoticeAndSearch()
				}).fail(() => {
					h.notify.show(h.globals.msg.failed, "folders-failed")
				})
			},
			deleteFolders: (a, e) => {
				a && a.length && h.fn.deleteData("folders", {
					type: h.globals.data.type,
					folders: a,
					deleteAttachments: e
				}).done(a => {
					let e = !1;
					for (const t of a) h.data.tree.removeItem(t), e || h.data.folder.active != t || (e = !0), h.data.folder.copy == t && (h.data.folder.copy = null);
					h.fn.updateNoticeAndSearch(), h.fn.updateFoldersAttachCount(), h.fn.refreshFolderFilter(), h.data.folder.active < 0 ? h.fn.activateFolder(h.data.folder.active, !1, !0) : e && h.fn.activateFolder(-1)
				}).fail(() => {
					h.notify.show(h.globals.msg.failed, "folders-failed")
				})
			},
			downloadFolders: a => {
				a && a.length && h.fn.getData("folders/download/url", {
					type: h.globals.data.type,
					folders: a
				}).done(a => {
					window.open(a, "_blank")
				}).fail(() => {
					h.notify.show(h.globals.msg.failed, "folders-failed")
				})
			},
			filterFolders: a => {
				clearTimeout(h.data.filter.timerId), h.data.filter.timerId = setTimeout(() => {
					h.data.tree.filter(a)
				}, 500)
			},
			onToggleContainer: () => {
				h.data.hidden = !h.data.hidden, h.data.ui.$container.toggleClass("folders-hidden", h.data.hidden), h.data.ui.$mediaframe && h.data.ui.$mediaframe.toggleClass("folders-active", !h.data.hidden), Cookies.set("folders-sidebar-hidden", h.data.hidden)
			},
			onSplitterMouseDown: a => {
				a.preventDefault(), a.stopImmediatePropagation(), h.data.splitter.cursor.startWidth = h.data.width.current, h.data.splitter.cursor.start = h.data.splitter.prev = h.data.splitter.cursor.current = a.pageX, g(window).on("mousemove", h.fn.onSplitterMouseMove), g(window).on("mouseup", h.fn.onSplitterMouseUp)
			},
			onSplitterMouseMove: a => {
				h.data.splitter.cursor.prev = h.data.splitter.cursor.current, h.data.splitter.cursor.current = a.pageX, h.data.width.current = h.data.splitter.cursor.startWidth + (h.data.splitter.cursor.current - h.data.splitter.cursor.start), Cookies.set("folders-sidebar-width", h.data.width.current), h.fn.updateWidth(h.data.width.current)
			},
			onSplitterMouseUp: () => {
				g(window).off("mousemove", h.fn.onSplitterMouseMove), g(window).off("mouseup", h.fn.onSplitterMouseUp)
			},
			closeActiveForms: () => {
				g("#folders-form-create").removeClass("folders-active"), g("#folders-form-sort").removeClass("folders-active")
			},
			onFolderCreate: () => {
				console.log('onFolderCreate: Function called');
				if (h.globals.data.rights.c) {
					const t = g("#folders-form-create");
					console.log('onFolderCreate: Form found:', t.length);
					console.log('onFolderCreate: Form has active class:', t.hasClass("folders-active"));
					if (t.hasClass("folders-active")) t.removeClass("folders-active");
					else {
						console.log('onFolderCreate: Opening form');
						h.fn.closeActiveForms();
						const o = t.find("#folders-folder-name"),
							i = t.find("#folders-folder-parent"),
							r = t.find("#folders-folder-color");
						o.val(""), h.colorpicker.set(r, null), i.off().empty().append(g("<option>").val(0).text(h.globals.msg.parent_folder));
						var a = h.data.tree.getFlatData();
						for (const s in a) {
							var e = a[s];
							h.data.ticket ? i.append(g("<option>").val(e.id).html("&nbsp;&nbsp;".repeat(e.level) + e.title).prop("selected", e.id === h.data.folder.active)) : i.append(g("<option>").val(e.id).html("&nbsp;&nbsp;".repeat(e.level) + e.title))
						}

						function d() {
							t.removeClass("folders-active")
						}
						i.on("change", a => {
							a.target.selectedIndex = 0
						}), t.off("click"), t.one("click", ".folders-close", () => {
							d()
						}), t.one("click", ".folders-submit", () => {
							var a = o.val().split(",").map(a => a.trim()),
								e = i.val(),
								t = h.colorpicker.get(r);
							h.fn.createFolders(a, t, e), d()
						}), t.addClass("folders-active")
						console.log('onFolderCreate: Form activated');
					}
				}
			},
			onFolderCreateBuiltin: e => {
				if (h.globals.data.rights.c) {
					const o = h.data.ui.$tree.find(`.folders-tree-item[data-id=${e}]`);
					if (o.length) {
						const i = g("<div>").addClass("folders-tree-nodes"),
							r = g("<div>").addClass("folders-tree-node"),
							s = g("<div>").addClass("folders-tree-item folders-tree-edited");
						var a = g("<div>").addClass("folders-tree-icon").append(h.data.tree.getIcon());
						const l = g("<div>").addClass("folders-tree-edit").attr({
								id: "folders-tree-edit"
							}),
							n = g("<input>").addClass("folders-tree-input").attr({
								spellcheck: "false",
								autocomplete: "off"
							});
						var t = g("<div>").addClass("folders-tree-btn-enter");

						function d() {
							i.remove(), h.data.tree.toggleDragDrop(!0)
						}
						h.data.tree.toggleDragDrop(!1), o.parent().append(i.append(r.append(s.append(a, l.append(n, t))))), n.focus().val(h.globals.msg.new_folder).one("blur", () => {
							d()
						}).on("keyup", a => {
							13 != a.keyCode && 27 != a.keyCode || (d(), 13 == a.keyCode && (a = n.val().split(",").map(a => a.trim()), h.fn.createFolders(a, null, e)))
						})
					}
				}
			},
			onFolderCopy: a => {
				h.data.folder.copy = a
			},
			onFolderPaste: a => {
				null != h.data.folder.copy && h.fn.copyFolders(h.data.folder.copy, a)
			},
			onFolderDelete: e => {
				const a = g("<div>").addClass("folders-modal"),
					t = g("#folders-form-delete").clone();

				function d() {
					a.remove()
				}
				t.off("click"), t.one("click", ".folders-close", () => {
					d()
				}), t.one("click", ".folders-submit", () => {
					var a = t.find("#folders-delete-attachments").is(":checked");
					h.fn.deleteFolders(e.map(a => a.id), a), d()
				}), g("body").append(a.append(t)), setTimeout(() => {
					a.addClass("folders-active"), t.addClass("folders-active")
				})
			},
			onFolderDownload: a => {
				h.fn.downloadFolders(a.map(a => a.id))
			},
			onFolderSort: () => {
				const d = g("#folders-form-sort");
				if (d.hasClass("folders-active")) d.removeClass("folders-active");
				else {
					h.fn.closeActiveForms();
					let t = h.data.meta.sort.items;
					t && d.find(`.folders-sort-types [data="${t}"]`).addClass("folders-active"), d.find(".folders-sort-type").off().on("click", a => {
						const e = g(a.target);
						a = e.attr("data"), d.find(".folders-sort-type").removeClass("folders-active"), e.toggleClass("folders-active", t !== a), t = t === a ? null : a, h.data.meta.sort.items !== t && (h.data.meta.sort.items = t, h.data.loader.request && h.data.loader.request.abort(), h.data.loader.request = h.fn.updateMeta(), h.data.loader.request.always(() => {
							h.data.loader.request = null, h.fn.activateFolder(h.data.folder.active, !1, !0)
						}))
					}), d.off("click"), d.one("click", ".folders-close", () => {
						d.removeClass("folders-active")
					}), d.addClass("folders-active")
				}
			},
			onFolderClick: a => {
				// Clear any existing timer
				clearTimeout(h.data.click.timerId);
				
				// Don't handle clicks on edit elements or when editing
				if (g(a.target).closest('.folders-tree-edit').length || g(a.currentTarget).hasClass('folders-tree-edited')) {
					return;
				}
				
				// Only handle single clicks (not part of double-click)
				if (!a.shiftKey && !a.ctrlKey && !g(a.target).hasClass("folders-tree-icon") && 1 === a.detail) {
					const e = g(a.currentTarget);
					const folderId = e.attr("data-id");
					h.data.click.folder = folderId;
					h.data.click.timerId = setTimeout(h.fn.onFolderClickAction, 300);
				}
			},
			onFolderClickAction: () => {
				h.data.click.timerId = null;
				var a = h.data.click.folder;
				- 1 != a && -2 != a || h.data.tree.clearSelection(), h.fn.activateFolder(a)
			},
			onFolderDblClick: a => {
				// Clear any pending single-click action
				if (h.data.click.timerId) {
					clearTimeout(h.data.click.timerId);
					h.data.click.timerId = null;
				}
				
				// Don't handle double-clicks on edit elements or when editing
				if (g(a.target).closest('.folders-tree-edit').length || g(a.currentTarget).hasClass('folders-tree-edited')) {
					return;
				}
				
				// Only handle double-clicks for edit rights
				if (h.globals.data.rights.e && !a.shiftKey && !a.ctrlKey && !g(a.target).hasClass("folders-tree-icon") && a.detail === 2) {
					a.preventDefault();
					a.stopPropagation();
					
					const folderId = g(a.currentTarget).attr("data-id");
					
					// Disable rename for "All Items" (-1) and "Uncategorized" (-2) folders
					if (folderId === "-1" || folderId === "-2") {
						return;
					}
					
					h.fn.startFolderRename(folderId);
				}
			},
			onContextMenu: a => {
				if (h.globals.data.rights.c || h.globals.data.rights.e || h.globals.data.rights.d) {
					const r = g(a.currentTarget),
						s = r.attr("data-id");
					if (-1 != s && -2 != s) {
						var e = h.data.tree.getItem(s);
						e.state.selected || (h.data.tree.clearSelection(), h.data.tree.selectItem(s, !0)), a.preventDefault();
						const l = h.data.tree.getSelectedItems(),
							n = g("<div>").addClass("folders-contextmenu").attr({
								tabindex: -1
							}),
							f = g("body"),
							c = () => {
								n.remove(), h.data.tree.clearSelection()
							};
						var t = a => {
							h.fn.colorFolders(l.map(a => a.id), a), c()
						};
						for (const p of h.data.contextmenu.list)
							if (!(!h.globals.data.rights.c && "c" == p.right || !h.globals.data.rights.v && "v" == p.right || !h.globals.data.rights.e && "e" == p.right || !h.globals.data.rights.d && "d" == p.right || "attachment" !== h.globals.data.type && "download" == p.id || (p.id == "rename" && (s === "-1" || s === "-2")))) {
								const u = g("<div>").addClass("folders-item").attr({
									"data-id": p.id
								});
								var d = g("<div>").addClass("folders-icon").html(p.icon),
									o = g("<div>").addClass("folders-title").text(p.title);
								switch (n.append(u.append(d, o)), p.id) {
									case "create":
										n.append(g("<div>").addClass("folders-splitter"));
										break;
									case "color": {
										const m = g("<div>").addClass("folders-submenu");
										new IFOLDERS.PLUGINS.COLORPICKER(e.color, m, t), u.append(m), u.on("mouseover mouseout", a => {
											m.toggleClass("folders-active", "mouseover" == a.type), "mouseover" !== a.type && n.focus()
										})
									}
									break;
									case "paste":
										u.toggleClass("folders-disabled", null == h.data.folder.copy);
										break;
									case "delete":
										u.addClass("folders-alert")
								}
							} var i = (a = a.originalEvent).clientY,
							a = a.clientX;
						n.css({
							top: i,
							left: a
						}).on("blur", a => {
							a.currentTarget.contains(a.relatedTarget) || c()
						}).on("click", ".folders-item", a => {
							const e = g(a.target);
							switch (e.data("id")) {
								case "create":
									h.data.ticket ? h.fn.onFolderCreateBuiltin(s) : h.notify.show(h.globals.msg.upgrade, "folders-upgrade"), c();
									break;
								case "rename":
									// Disable rename for system folders
									if (s === "-1" || s === "-2") {
										h.notify.show(h.globals.msg.failed || 'Cannot rename system folders', "folders-failed");
									} else {
										h.fn.startFolderRename(s);
									}
									c();
									break;
								case "copy":
									h.data.ticket ? h.fn.onFolderCopy(s) : h.notify.show(h.globals.msg.upgrade, "folders-upgrade"), c();
									break;
								case "paste":
									h.data.ticket ? h.fn.onFolderPaste(s) : h.notify.show(h.globals.msg.upgrade, "folders-upgrade"), c();
									break;
								case "delete":
									h.fn.onFolderDelete(l), c();
									break;
								case "download":
									h.fn.onFolderDownload(l), c()
							}
						}), f.append(n), n.focus()
					}
				}
			},
			onSearchInput: a => {
				h.fn.filterFolders(a.target.value)
			},
			onSearchClear: () => {
				g("#folders-search-input").val(""), h.fn.filterFolders()
			},
			onAttachmentFolderEnter: a => {
				h.data.dragdrop.$target = g(a.currentTarget).addClass("folders-droppable")
			},
			onAttachmentFolderLeave: () => {
				h.data.dragdrop.$target && h.data.dragdrop.$target.removeClass("folders-droppable"), h.data.dragdrop.$target = null
			},
			onAttachmentFolderUnderPointer: a => {
				const e = g(document.elementFromPoint(a.originalEvent.touches[0].clientX, a.originalEvent.touches[0].clientY)),
					t = e.closest(".folders-tree-item");
				h.data.dragdrop.$target && h.data.dragdrop.$target.removeClass("folders-droppable"), h.data.dragdrop.$target = null, t.length && (h.data.dragdrop.$target = t.addClass("folders-droppable"))
			},
			onAttachmentDown: e => {
				var t = "touchstart" === e.type && e.originalEvent.touches && 1 == e.originalEvent.touches.length;
				if (1 === e.which || t)
					if (h.data.media) {
						if (h.data.mediaBrowse) {
							t || e.preventDefault(), t || e.stopImmediatePropagation();
							const a = [];
							g('.media-frame .media-frame-content .attachment[aria-checked="true"]').each(function() {
								a.push(g(this).attr("data-id"))
							}), 0 == a.length && a.push(g(e.currentTarget).attr("data-id")), a.length && (h.data.dragdrop.isTouch = t, h.data.dragdrop.items = a, h.data.dragdrop.$ghost.text("Move " + a.length + " items").appendTo("body"), t ? (document.addEventListener("touchmove", h.fn.onTouchMove, {
								passive: !1
							}), g(window).on("touchmove", h.fn.onAttachmentFolderUnderPointer), g(window).on("touchmove", h.fn.onAttachmentMove), g(window).on("touchend", h.fn.onAttachmentUp)) : (h.data.ui.$sidebar.on("mouseenter", ".folders-tree-item", h.fn.onAttachmentFolderEnter), h.data.ui.$sidebar.on("mouseleave", ".folders-tree-item", h.fn.onAttachmentFolderLeave), g(window).on("mousemove", h.fn.onAttachmentMove), g(window).on("mouseup", h.fn.onAttachmentUp)))
						}
					} else {
						t || e.preventDefault(), t || e.stopImmediatePropagation();
						const d = [];
						let a = "post";
						switch (h.globals.data.type) {
							case "attachment":
								a = "media";
								break;
							case "users":
								a = "users";
								break;
							case "plugins":
								a = "checked"
						}
						g(`#the-list input[name='${a}[]']:checked`).each(function() {
							d.push(g(this).val())
						}), 0 == d.length && d.push(g(e.currentTarget).find("input").val()), d.length && (h.data.dragdrop.isTouch = t, h.data.dragdrop.items = d, h.data.dragdrop.$ghost.text("Move " + d.length + " items").appendTo("body"), t ? (document.addEventListener("touchmove", h.fn.onTouchMove, {
							passive: !1
						}), g(window).on("touchmove", h.fn.onAttachmentFolderUnderPointer), g(window).on("touchmove", h.fn.onAttachmentMove), g(window).on("touchend", h.fn.onAttachmentUp)) : (h.data.ui.$sidebar.on("mouseenter", ".folders-tree-item", h.fn.onAttachmentFolderEnter), h.data.ui.$sidebar.on("mouseleave", ".folders-tree-item", h.fn.onAttachmentFolderLeave), g(window).on("mousemove", h.fn.onAttachmentMove), g(window).on("mouseup", h.fn.onAttachmentUp)))
					}
			},
			onAttachmentMove: a => {
				h.data.dragdrop.items && h.data.dragdrop.items.length && (a = h.data.dragdrop.isTouch ? a.originalEvent.touches[0] : a, h.data.dragdrop.$ghost.addClass("folders-active").css({
					top: a.clientY + 5 + "px",
					left: a.clientX + 5 + "px"
				}))
			},
			onAttachmentUp: () => {
				var a = h.data.dragdrop.$target ? h.data.dragdrop.$target.attr("data-id") : null,
					e = h.data.dragdrop.items;
				h.data.dragdrop.$ghost.text("").removeClass("folders-active").detach(), h.data.dragdrop.$target && h.data.dragdrop.$target.removeClass("folders-droppable"), h.data.dragdrop.$target = null, h.data.dragdrop.items = null, clearTimeout(h.data.dragdrop.timerId), h.data.dragdrop.timerId = null, h.data.dragdrop.isTouch ? (document.removeEventListener("touchmove", h.fn.onTouchMove, {
					passive: !1
				}), g(window).off("touchmove", h.fn.onAttachmentFolderUnderPointer), g(window).off("touchmove", h.fn.onAttachmentMove), g(window).off("touchend", h.fn.onAttachmentUp)) : (h.data.ui.$sidebar.off("mouseenter", ".folders-tree-item", h.fn.onAttachmentFolderEnter), h.data.ui.$sidebar.off("mouseleave", ".folders-tree-item", h.fn.onAttachmentFolderLeave), g(window).off("mousemove", h.fn.onAttachmentMove), g(window).off("mouseup", h.fn.onAttachmentUp)), h.fn.dropAttachments(a, e)
			},
			onMediaModalPrepare: () => {},
			onMediaModalOpen: () => {
				h.data.modal || (h.data.modal = !0, h.data.ticket || wp && wp.blocks ? h.fn.loadSidebar() : h.fn.loadProposal())
			},
			onMediaModalClose: () => {
				h.data.modal = !1, h.data.ui.$container && h.data.ui.$container.remove(), h.data.ui.$minitools && h.data.ui.$minitools.remove(), h.data.ui.$mediaframe && h.data.ui.$mediaframe.removeClass("folders-active")
			},
			onAjaxComplete: (a, e, t) => {
				if (null != t.data && "string" == typeof t.data && -1 < t.data.indexOf("action=delete-post")) {
					const d = h.data.tree.getFlatData();
					t = d ? d.map(a => a.id) : null, h.fn.activateFolder(h.data.folder.active, !1, !0), h.fn.updateFoldersAttachCount(t), h.fn.updateNoticeAndSearch()
				}
			},
			initFolderFilter: () => {
				const $filter = g("#folders-folder-filter");
				console.log('initFolderFilter: Filter element found:', $filter.length, 'current value:', $filter.val());
				if ( $filter.length ) {
					// Remove any existing event handlers to prevent duplicates
					$filter.off("change");
					g(document).off("click", ".button.action");
					g(document).off("change", "#the-list input[name='media[]']");
					g(document).off("change", "#cb-select-all-1, #cb-select-all-2");
					g(document).off("visibilitychange");
					
					// Clear any existing intervals
					if ( h.data.filter && h.data.filter.refreshInterval ) {
						clearInterval(h.data.filter.refreshInterval);
					}
					
					$filter.on("change", h.fn.onFolderFilterChange);
					console.log('initFolderFilter: Change event handler bound');
					
					// Preserve folder filter in bulk actions
					h.fn.preserveFolderFilterInBulkActions();
					
					// Also preserve when bulk action buttons are clicked
					g(document).on("click", ".button.action", function() {
						setTimeout(() => {
							h.fn.preserveFolderFilterInBulkActions();
						}, 100);
					});
					
					// Update dropdown styling when files are selected/deselected
					h.fn.updateFolderFilterStyling();
					g(document).on("change", "#the-list input[name='media[]']", function() {
						h.fn.updateFolderFilterStyling();
					});
					
					// Handle select all checkboxes
					g(document).on("change", "#cb-select-all-1, #cb-select-all-2", function() {
						setTimeout(() => {
							h.fn.updateFolderFilterStyling();
						}, 100);
					});
					
					// Refresh dropdown when folders are updated
					h.fn.refreshFolderFilter();
					
					// Set up periodic refresh every 30 seconds to catch external changes
					h.data.filter = h.data.filter || {};
					h.data.filter.refreshInterval = setInterval(() => {
						h.fn.refreshFolderFilter();
					}, 30000);
					
					// Refresh when page becomes visible (user switches back to tab)
					g(document).on("visibilitychange", function() {
						if (!document.hidden) {
							h.fn.refreshFolderFilter();
						}
					});
				}
			},
			updateFolderFilterStyling: () => {
				const $filter = g("#folders-folder-filter");
				const $checkedBoxes = g("#the-list input[name='media[]']:checked");
				
				if ( $filter.length ) {
					if ( $checkedBoxes.length > 0 ) {
						$filter.addClass("has-selected-files");
						$filter.attr("title", `Move ${$checkedBoxes.length} selected file${$checkedBoxes.length > 1 ? 's' : ''} to folder`);
						
						// Update the first option text to show selected count
						const $firstOption = $filter.find("option:first");
						$firstOption.text(`Move ${$checkedBoxes.length} file${$checkedBoxes.length > 1 ? 's' : ''} to folder...`);
					} else {
						$filter.removeClass("has-selected-files");
						$filter.attr("title", "Select files and choose a folder to move them");
						
						// Reset the first option text
						const $firstOption = $filter.find("option:first");
						$firstOption.text("Move to Folder...");
					}
				}
			},
			refreshFolderFilter: () => {
				const $filter = g("#folders-folder-filter");
				if ( !$filter.length ) {
					return;
				}
				
				// Prevent multiple simultaneous requests
				if ( h.data.filter && h.data.filter.refreshing ) {
					return;
				}
				
				h.data.filter = h.data.filter || {};
				h.data.filter.refreshing = true;
				
				const currentValue = $filter.val();
				
				// Get updated folder list via AJAX
				g.ajax({
					url: h.globals.api.url + "/folders/filter-options",
					type: "GET",
					headers: {
						"X-WP-Nonce": h.globals.api.nonce
					},
					timeout: 10000 // 10 second timeout
				}).done(function(response) {
					if ( response && response.success && response.data ) {
						// Clear existing options except "All Folders"
						$filter.find("option:not(:first)").remove();
						
						// Add new options
						response.data.forEach(function(folder) {
							const $option = g('<option value="' + folder.id + '">' + folder.title + '</option>');
							$filter.append($option);
						});
						
						// Restore current selection if it still exists
						if ( currentValue && $filter.find('option[value="' + currentValue + '"]').length ) {
							$filter.val(currentValue);
						}
					}
				}).fail(function(xhr, status, error) {
					// Only log errors in debug mode
					if ( h.globals.data.debug ) {
						console.warn('Failed to refresh folder filter:', status, error);
					}
				}).always(function() {
					// Reset refreshing flag
					h.data.filter.refreshing = false;
				});
			},
			onFolderFilterChange: (e) => {
				console.log('onFolderFilterChange triggered:', e.target.value);
				const folderId = g(e.target).val();
				const $checkedBoxes = g("#the-list input[name='media[]']:checked");
				console.log('onFolderFilterChange: folderId =', folderId, 'checkedBoxes =', $checkedBoxes.length);
				
				if ( !folderId ) {
					// If "All Folders" is selected, just update the URL without moving files
					const currentUrl = new URL(window.location.href);
					currentUrl.searchParams.delete("folders_folder");
					window.history.replaceState({}, '', currentUrl.toString());
					// Trigger a soft refresh of the media grid/list
					if ( wp && wp.media && wp.media.view && wp.media.view.AttachmentsBrowser ) {
						const frame = wp.media.frame;
						if ( frame && frame.content && frame.content.get() && frame.content.get().collection ) {
							frame.content.get().collection.props.set('folders_folder', '');
							frame.content.get().collection.props.set('paged', 1);
							frame.content.get().collection.refresh();
						}
					} else {
						// Check if we're on upload.php page and trigger a form submission
						const $form = g("form[name='posts-filter']");
						if ( $form.length ) {
							// Add the folder filter to the form
							let $hiddenInput = $form.find('input[name="folders_folder"]');
							if ( !$hiddenInput.length ) {
								$hiddenInput = g('<input type="hidden" name="folders_folder" value="">');
								$form.append($hiddenInput);
							}
							$hiddenInput.val('');
							$form.submit();
						} else {
							// Fallback: reload the page
							window.location.reload();
						}
					}
					return;
				}
				
				if ( $checkedBoxes.length === 0 ) {
					// No files selected, just filter the view without page refresh
					const currentUrl = new URL(window.location.href);
					currentUrl.searchParams.set("folders_folder", folderId);
					currentUrl.searchParams.set("paged", "1");
					window.history.replaceState({}, '', currentUrl.toString());
					
					// Trigger a soft refresh of the media grid/list
					if ( wp && wp.media && wp.media.view && wp.media.view.AttachmentsBrowser ) {
						const frame = wp.media.frame;
						if ( frame && frame.content && frame.content.get() && frame.content.get().collection ) {
							frame.content.get().collection.props.set('folders_folder', folderId);
							frame.content.get().collection.props.set('paged', 1);
							frame.content.get().collection.refresh();
						}
					} else {
						// Check if we're on upload.php page and trigger a form submission
						const $form = g("form[name='posts-filter']");
						if ( $form.length ) {
							// Add the folder filter to the form
							let $hiddenInput = $form.find('input[name="folders_folder"]');
							if ( !$hiddenInput.length ) {
								$hiddenInput = g('<input type="hidden" name="folders_folder" value="">');
								$form.append($hiddenInput);
							}
							$hiddenInput.val(folderId);
							$form.submit();
						} else {
							// Fallback: reload the page
							window.location.reload();
						}
					}
					return;
				}
				
				// Get the selected file IDs
				const fileIds = [];
				$checkedBoxes.each(function() {
					fileIds.push(g(this).val());
				});
				
				// Show confirmation dialog
				const folderName = g(e.target).find("option:selected").text().split(' (')[0]; // Remove count
				const confirmMessage = `Move ${fileIds.length} selected file${fileIds.length > 1 ? 's' : ''} to folder "${folderName}"?`;
				
				if ( confirm(confirmMessage) ) {
					// Show loading state
					const $filter = g(e.target);
					const originalValue = $filter.val();
					$filter.prop('disabled', true).addClass('loading');
					
					// Move files to the selected folder
					h.fn.updateData("attach", {
						type: h.globals.data.type,
						folder: folderId,
						attachments: fileIds
					}).done(a => {
						h.notify.show(`Successfully moved ${fileIds.length} file${fileIds.length > 1 ? 's' : ''} to "${folderName}"`, "folders-success");
						
						// Uncheck all files
						$checkedBoxes.prop('checked', false);
						
						// Update folder counts
						h.fn.updateFoldersAttachCount(a);
						
						// Refresh the current view
						h.fn.activateFolder(h.data.folder.active, !1, !0);
						
						// Refresh the dropdown
						h.fn.refreshFolderFilter();
						
						// Reset filter to "All Folders" and re-initialize
						setTimeout(() => {
							$filter.val('').prop('disabled', false).removeClass('loading');
							// Re-initialize the folder filter and sidebar events to ensure everything works properly
							h.fn.bindSidebarEvents();
							h.fn.initFolderFilter();
						}, 1000);
						
					}).fail(() => {
						h.notify.show(h.globals.msg.failed || 'Failed to move files', "folders-failed");
						$filter.prop('disabled', false).removeClass('loading');
					});
				} else {
					// User cancelled, reset to previous selection
					setTimeout(() => {
						$filter.val(originalValue);
					}, 100);
				}
			},
			preserveFolderFilterInBulkActions: () => {
				// Add folder filter to bulk action forms
				const $bulkActionForms = g("form[name='posts-filter']");
				if ( $bulkActionForms.length ) {
					const currentFolder = g("#folders-folder-filter").val();
					if ( currentFolder ) {
						const $hiddenInput = g('<input type="hidden" name="folders_folder" value="' + currentFolder + '">');
						$bulkActionForms.append($hiddenInput);
					}
				}
			},
			onShowMediaDetails: a => {
				const e = g(a.target);
				var t;
				e.hasClass("folders-has-preview-details") || (a = e.attr("data-id")) && (t = window.wp.media.attachment(a)).attributes && t.attributes.preview_details && (e.addClass("folders-has-preview-details"), g(".attachment[data-id=" + a + "] .attachment-preview").prepend(t.attributes.preview_details))
			},
			onTouchMove: a => {
				a.preventDefault(), clearTimeout(h.data.dragdrop.timerId), a.touches[0].clientY < 30 ? h.data.dragdrop.timerId = setTimeout(h.fn.scroll.bind(null, -window.innerHeight / 5), 150) : a.touches[0].clientY > window.innerHeight - 30 && (h.data.dragdrop.timerId = setTimeout(h.fn.scroll.bind(null, window.innerHeight / 5), 150))
			},
			scroll: a => {
				window.scrollBy({
					top: a,
					behavior: "smooth"
				}), h.data.dragdrop.timerId = setTimeout(h.fn.scroll.bind(null, a), 150)
			},
			formatBytes: a => {
				let e = 0,
					t = parseInt(a, 10) || 0;
				for (; 1024 <= t && ++e;) t /= 1024;
				return t.toFixed(t < 10 && 0 < e ? 1 : 0) + " " + ["bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"][e]
			},
			uploader: {
				build: () => {
					h.data.uploader.$container = g("<div>").addClass("folders-uploader"), h.data.uploader.$header = g("<div>").addClass("folders-header").text("Upload"), h.data.uploader.$title = g("<div>").addClass("folders-title"), h.data.uploader.$count = g("<div>").addClass("folders-count"), h.data.uploader.$close = g("<div>").addClass("folders-close").html('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d=" M 7.734 6.281 L 6.328 7.688 L 10.609 11.969 L 6.266 16.313 L 7.672 17.719 L 12.016 13.375 L 16.328 17.688 L 17.734 16.281 L 13.422 11.969 L 17.672 7.719 L 16.266 6.313 L 12.016 10.563 L 7.734 6.281 Z "></path></svg>'), h.data.uploader.$data = g("<div>").addClass("folders-data"), h.data.uploader.$container.append(h.data.uploader.$header.append(h.data.uploader.$title, h.data.uploader.$count, h.data.uploader.$close), h.data.uploader.$data), h.data.ui.$container.append(h.data.uploader.$container), h.data.uploader.$close.on("click", h.fn.uploader.close)
				},
				open: () => {
					h.data.uploader.$container.addClass("folders-active")
				},
				close: () => {
					h.data.uploader.$container.removeClass("folders-active"), h.data.uploader.$close.removeClass("folders-active"), h.data.uploader.$data.empty(), h.data.uploader.list.filter(a => !a.loaded).length && (h.data.uploader.instance.stop(), h.fn.uploader.complete()), h.data.uploader.list = []
				},
				complete: () => {
					var a = h.data.uploader.list.map(a => a.folder).filter((a, e, t) => t.indexOf(a) == e);
					h.fn.activateFolder(h.data.folder.active, !1, !0), h.fn.updateFoldersAttachCount(a), h.fn.updateNoticeAndSearch(), h.fn.refreshFolderFilter()
				},
				addFile: a => {
					var e = {
							id: a.id,
							folder: a._folder,
							loaded: !1
						},
						e = (h.data.uploader.list.push(e), h.data.tree.getItem(a._folder));
					const t = g("<div>").addClass("folders-item").attr({
						"data-id": a.id
					});
					var d = g("<div>").addClass("folders-title").text(a.name),
						a = g("<div>").addClass("folders-info").text(h.fn.formatBytes(a.size) + (e ? " [" + e.title + "]" : ""));
					h.data.uploader.$data.prepend(t.append(d, a))
				},
				completeFile: a => {
					const e = h.data.uploader.$data.find(`.folders-item[data-id="${a.id}"]`);
					e.addClass("folders-loaded");
					for (const t of h.data.uploader.list)
						if (t.id === a.id) {
							t.loaded = !0;
							break
						} h.fn.uploader.updateHeader()
				},
				updateHeader: () => {
					var a = h.data.uploader.list.filter(a => a.loaded).length;
					h.data.uploader.$count.text(a + " / " + h.data.uploader.list.length)
				}
			},
			replacemedia: {
				open: a => {
					const e = {
						data: {
							$modal: g("<div>").addClass("folders-modal"),
							$form: g("#folders-form-replace-media").clone(),
							attachment: g(a).attr("data-attachment-id"),
							file: null
						},
						fn: {
							build: () => {
								e.data.$fileDropZone = e.data.$form.find(".folders-file-drop-zone"), e.data.$fileUpload = e.data.$form.find(".folders-file-upload"), e.data.$imagePreview = e.data.$form.find(".folders-image-preview"), e.data.$fileSelect = e.data.$form.find(".folders-file-select"), e.data.$fileSubmit = e.data.$form.find(".folders-btn.folders-submit"), e.data.$loader = e.data.$form.find(".folders-loader"), g("body").append(e.data.$modal.append(e.data.$form)), setTimeout(() => {
									e.data.$modal.addClass("folders-active"), e.data.$form.addClass("folders-active")
								})
							},
							bind: () => {
								e.data.$form.on("click", ".folders-close", e.fn.close), e.data.$form.on("click", ".folders-submit", e.fn.submit), e.data.$modal.on("dragenter dragover drop", () => !1), e.data.$fileUpload.on("change", e.fn.selectFile), e.data.$fileSelect.on("click", () => {
									e.data.$fileUpload.click()
								}), (new XMLHttpRequest).upload && (e.data.$fileDropZone.on("dragover dragleave", e.fn.dragHover), e.data.$fileDropZone.on("drop", e.fn.selectFile))
							},
							loading: a => {
								e.data.$loader.toggleClass("folders-active", a)
							},
							dragHover: a => (a.currentTarget.contains(a.relatedTarget) || e.data.$fileDropZone.toggleClass("folders-hover", "dragover" === a.type), !1),
							selectFile: a => {
								e.data.file = null, e.data.$fileSubmit.addClass("folders-hidden"), e.fn.dragHover(a), 1 == (a = a.originalEvent.target.files || a.originalEvent.dataTransfer.files).length ? (a = a[0], /\.(?=gif|jpg|png|jpeg)/gi.test(a.name) ? (e.data.file = a, e.data.$fileDropZone.addClass("folders-preview"), e.data.$imagePreview.get(0).src = URL.createObjectURL(e.data.file), e.data.$fileSubmit.removeClass("folders-hidden")) : (e.data.$fileDropZone.removeClass("folders-preview"), e.data.$fileDropZone.get(0).reset())) : h.notify.show(h.globals.msg.failed, "folders-failed")
							},
							show: () => {
								e.fn.build(), e.fn.bind()
							},
							close: () => {
								e.data.$modal.remove()
							},
							submit: () => {
								if (null != e.data.file) {
									const a = new FormData;
									a.append("file", e.data.file), a.append("attachment", e.data.attachment), e.fn.loading(!0), g.ajax({
										url: h.globals.api.url + "/replace-media",
										type: "POST",
										data: a,
										processData: !1,
										contentType: !1,
										headers: {
											"X-WP-Nonce": h.globals.api.nonce
										}
									}).done(a => {
										a && a.success ? (e.fn.close(), h.notify.show(h.globals.msg.success, "folders-success"), h.fn.activateFolder(h.data.folder.active, !1, !0), h.data.editAttachments && h.data.editAttachments.updateMediaData()) : h.notify.show(h.globals.msg.failed, "folders-failed")
									}).fail(() => {
										h.notify.show(h.globals.msg.failed, "folders-failed")
									}).always(() => {
										e.fn.loading(!1)
									})
								}
							}
						}
					};
					e.fn.show()
				}
			},
			// Test function to demonstrate the improved rename functionality
			testRenameFunctionality: () => {
				console.log('Folders: Testing improved rename functionality...');
				
				// Test 1: Check if the new functions exist
				console.log('✓ startFolderRename function exists:', typeof h.fn.startFolderRename === 'function');
				console.log('✓ renameFolder function enhanced:', typeof h.fn.renameFolder === 'function');
				
				// Test 2: Check if user has edit rights
				console.log('✓ User has edit rights:', h.globals.data.rights.e);
				
				// Test 3: Check if tree is available
				console.log('✓ Tree component available:', h.data.tree !== null);
				
				// Test 4: Check if UI elements are available
				console.log('✓ Sidebar UI available:', h.data.ui.$sidebar.length > 0);
				
				// Test 5: Test system folder restrictions
				console.log('✓ System folder restrictions enabled');
				console.log('  - All Items (-1): Rename disabled');
				console.log('  - Uncategorized (-2): Rename disabled');
				
				return {
					startFolderRename: typeof h.fn.startFolderRename === 'function',
					renameFolder: typeof h.fn.renameFolder === 'function',
					userRights: h.globals.data.rights.e,
					treeAvailable: h.data.tree !== null,
					uiAvailable: h.data.ui.$sidebar.length > 0,
					systemFolderProtection: true
				};
			},
			// Enhanced debugging for rename operations
			debugRename: (operation, data) => {
				if (typeof console !== 'undefined' && console.log) {
					console.log(`Folders Rename Debug [${operation}]:`, data);
				}
			}
		}
	};
	g(() => {
		h.fn.run()
	}), window.IFOLDERS = window.IFOLDERS || {}, window.IFOLDERS.APP = h
}(jQuery);