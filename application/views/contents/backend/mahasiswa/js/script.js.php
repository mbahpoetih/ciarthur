<script>
	const BASE_URL = "<?= base_url($uri_segment) ?>"
	let datatable, $insert, $update, $delete, $import_excel,
		$export_excel, $export_pdf, $export_word,
		map, map_modal, marker_modal, legend;

	// Document ready
	$(() => {
		/**
		 * Keperluan WebGIS dengan Leaflet
		 * 
		 */
		// ================================================== //

		const initMap = () => {
			if (map) map.remove()
			map = L.map("map", {
				center: [-7.5828, 111.0444],
				zoom: 12,
				layers: [
					/** OpenStreetMap Tile Layer */
					L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
						attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
					}),
				],
				scrollWheelZoom: false,
			})

			/** Legend */
			legend = L.control({
				position: "bottomleft"
			})

			legend.onAdd = (map) => {
				let div = L.DomUtil.create("div", "legend");
				div.innerHTML += "<h3><b>KABUPATEN KARANGANYAR</b></h3>";
				return div;
			}

			legend.addTo(map)

			/** GeoJSON Features */
			$.getJSON(BASE_URL + 'ajax_get_geojson',
				response => {
					let geojson = L.geoJSON(response, {
						onEachFeature: (feature, layer) => {
							layer.on({
								mouseover: (event) => {
									let layer = event.target;
									layer.setStyle({
										weight: 5,
										dashArray: '',
										fillOpacity: 0.7
									});
									if (!L.Browser.ie && !L.Browser.opera &&
										!L.Browser.edge) {
										layer.bringToFront();
									}
								},
								mouseout: (event) => {
									geojson.resetStyle(event.target)
								},
								click: (event) => {
									map.fitBounds(event
										.target
										.getBounds()
									);
								}
							})
						}
					}).addTo(map)
				})

			axios.get(BASE_URL + 'ajax_get_kecamatan')
				.then(res => {
					let results = res.data.data
					results.map(item => {
						if (item.latitude && item.longitude) {
							L.marker([item.latitude, item.longitude])
								.addTo(map)
								.bindPopup(
									new L.Popup({
										autoClose: false,
										closeOnClick: false
									})
									.setContent(`<b>${item.nama}</b>`)
									.setLatLng([item.latitude, item.longitude])
								).openPopup();
						}
					})
				})

			axios.get(BASE_URL + 'ajax_get_latlng')
				.then(res => {
					let results = res.data.data
					results.map(item => {
						if (item.latitude && item.longitude) {
							L.marker([item.latitude, item.longitude], {
									icon: L.icon({
										iconUrl: 'https://upload.wikimedia.org/wikipedia/commons/f/f2/678111-map-marker-512.png',
										iconSize: [
											40, 40
										], // size of the icon
										iconAnchor: [
											20, 40
										], // point of the icon which will correspond to marker's location
										popupAnchor: [
											0, -30
										] // point from which the popup should open relative to the iconAnchor
									})
								})
								.addTo(map)
								.bindPopup(
									new L.Popup({
										autoClose: false,
										closeOnClick: false
									})
									.setContent(`<b>${item.nama}</b>`)
									.setLatLng([
										item.latitude, item.longitude
									])
								).openPopup();
						}
					})
				})
		}

		const map_in_swal = (status) => {

			if (map_modal) map_modal.remove()
			map_modal = L.map(`map-${status}`, {
				center: [-7.5828, 111.0444],
				zoom: 12,
				layers: [
					/** OpenStreetMap Tile Layer */
					L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
						attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
					}),

				],
				scrollWheelZoom: false,
			})

			setTimeout(() => {
				map_modal.invalidateSize()
			}, 500);

			map_modal.on('click', (event) => {
				if (marker_modal) map_modal.removeLayer(marker_modal)
				marker_modal = L.marker([event.latlng.lat, event.latlng
					.lng
				], { //-7.641355, 111.0377783
					icon: L.icon({
						iconUrl: 'https://upload.wikimedia.org/wikipedia/commons/f/f2/678111-map-marker-512.png',
						iconSize: [40, 40], // size of the icon
						iconAnchor: [
							20, 40
						], // point of the icon which will correspond to marker's location
						popupAnchor: [
							0, -30
						] // point from which the popup should open relative to the iconAnchor
					})
				})
				marker_modal.addTo(map_modal)
				marker_modal.bindPopup(`${event.latlng.lat}, ${event.latlng.lng}`).openPopup()

				$(`#input_${status}_latitude`).val(event.latlng.lat)
				$(`#input_${status}_longitude`).val(event.latlng.lng)
			})
		}

		initMap() // Init leaflet map

		/**
		 * Keperluan DataTable, Datepicker, Summernote dan BsCustomFileInput
		 */
		// ================================================== //
		datatable = $('#datatable').DataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			// responsive: true,
			dom: `<"dt-custom-filter mb-3 d-block">
                <"d-flex flex-row justify-content-end flex-wrap mb-2"B>
                <"d-flex flex-row justify-content-between"lf>
                rt
                <"d-flex flex-row justify-content-between"ip>`,
			buttons: {
				/** Tombol-tombol Export & Tambah Data */
				buttons: [{
						className: 'btn btn-primary m-2',
						text: $('<i>', {
							class: 'fa fa-file-word-o'
						}).prop('outerHTML') + ' Export DOCX', // Export DOCX
						action: (e, dt, node, config) => {
							// location.replace(BASE_URL + 'export_word');
							$export_word()
						}
					},
					{
						className: 'btn btn-danger m-2',
						text: $('<i>', {
							class: 'fa fa-file-pdf-o'
						}).prop('outerHTML') + ' Export PDF', // Export PDF
						action: (e, dt, node, config) => {
							// location.replace(BASE_URL + 'export_pdf');
							$export_pdf()
						}
					},
					{
						className: 'btn btn-success m-2',
						text: $('<i>', {
							class: 'fa fa-file-excel-o'
						}).prop('outerHTML') + ' Export XLSX', // Export XLSX
						action: (e, dt, node, config) => {
							// location.replace(BASE_URL + 'export_excel');
							$export_excel()
						}
					},
					{
						className: 'btn btn-success m-2 <?php if (is_denied('create-mahasiswa')) : ?>d-none<?php endif ?>',
						text: $('<i>', {
							class: 'fa fa-upload'
						}).prop('outerHTML') + ' Import XLSX', // Import XLSX
						action: (e, dt, node, config) => {
							$import_excel()
						}
					},
					{
						className: "btn btn-info m-2 text-white <?php if (is_denied('create-mahasiswa')) : ?>d-none<?php endif ?>",
						text: $('<i>', {
							class: 'fa fa-plus',
						}).prop('outerHTML') + ' Tambah Data', // Tambah Data
						action: (e, dt, node, config) => {
							// $('#modal_tambah').modal('show');
							$insert();
						}
					},
				],
				dom: {
					button: {
						className: 'btn'
					},
					buttonLiner: {
						tag: null
					}
				}
			},
			ajax: {
				url: BASE_URL + 'data',
				type: 'GET',
				dataType: 'JSON',
				data: {},
				beforeSend: () => {
					loading()
				},
				complete: () => {
					setTimeout(async () => {
						await Swal.hideLoading()
						await Swal.close()
					}, 100);
				}
			},
			columnDefs: [{
					targets: [0, 1, 2, 3, 4, 5, 6, 7, 8], // Sesuaikan dengan jumlah kolom
					className: 'text-center'
				},
				{
					targets: [0, 1, 7, 8],
					searchable: false,
					orderable: false,
				},
				{
					targets: [9],
					visible: false,
					searchable: false,
				}
			],
			order: [
				[9, 'desc']
			],
			columns: [{ // 0
					title: '#',
					name: '#',
					data: 'DT_RowIndex',
				},
				{ // 1
					title: 'Foto',
					name: 'foto',
					data: 'foto',
					render: (foto) => {
						return $('<img>', {
							src: `<?= base_url() ?>img/mahasiswa/${foto}?w=100&h=200&fit=crop`,
							alt: 'Foto'
						}).prop('outerHTML')
					}
				},
				{ // 2
					title: 'NIM',
					name: 'nim',
					data: 'nim',
				},
				{ // 3
					title: 'Nama',
					name: 'nama',
					data: 'nama',
					render: (nama) => {
						return $('<span>', {
							html: nama,
							class: 'nama'
						}).prop('outerHTML')
					}
				},
				{ // 4
					title: 'Program Studi',
					name: 'nama_prodi',
					data: 'nama_prodi',
				},
				{ // 5
					title: 'Fakultas',
					name: 'nama_fakultas',
					data: 'nama_fakultas',
				},
				{ // 6
					title: 'Angkatan',
					name: 'angkatan',
					data: 'angkatan',
				},
				{ // 7
					title: 'LatLng',
					name: 'latlng',
					data: (data) => {
						return $('<span>', {
								class: 'badge badge-primary',
								html: data.latitude ? data.latitude : '-'
							}).prop('outerHTML') + '<br>' +
							$('<span>', {
								class: 'badge badge-primary',
								html: data.longitude ? data.longitude : '-'
							}).prop('outerHTML')
					}
				},
				{ // 8
					title: 'Aksi',
					name: 'id',
					data: 'id',
					render: (id) => {
						let btn_edit = $('<button>', {
							type: 'button',
							class: "btn btn-success btn_edit <?php if (is_denied('update-mahasiswa')) : ?>d-none<?php endif ?>",
							'data-id': id,
							html: $('<i>', {
								class: 'fa fa-edit'
							}).prop('outerHTML'),
							title: 'Ubah Data'
						})

						let btn_delete = $('<button>', {
							type: 'button',
							class: "btn btn-danger btn_delete <?php if (is_denied('delete-mahasiswa')) : ?>d-none<?php endif ?>",
							'data-id': id,
							html: $('<i>', {
								class: 'fa fa-trash'
							}).prop('outerHTML'),
							title: 'Hapus Data'
						})

						return $('<div>', {
							role: 'group',
							class: 'btn-group btn-group-sm',
							html: [btn_edit, btn_delete]
						}).prop('outerHTML')
					}
				},
				{ // 9
					title: 'Created At',
					name: 'created_at',
					data: 'created_at',
				}
			],
			initComplete: function(event) {
				$(this).on('click', '.btn_edit', function(event) {
					event.preventDefault()
					$update(this);
				});

				$(this).on('click', '.btn_delete', function(event) {
					event.preventDefault()
					$delete(this);
				});

				/** Elemen - elemen filter */
				$('.dt-custom-filter').html((index, currentContent) => {
					// Filter tanggal
					let filter_tanggal = $('<div>', {
						class: 'col-md-4',
						html: [
							$('<label>', {
								for: 'filter_tanggal',
								html: 'Tanggal',
							}),
							$('<input>', {
								autocomplete: 'off',
								type: 'text',
								id: 'filter_tanggal',
								name: 'filter_tanggal',
								class: 'form-control datepicker',
								placeholder: 'Pilih Tanggal'
							})
						]
					})

					// Filter fakultas
					let filter_fakultas = $('<div>', {
						class: 'col-md-4',
						html: [
							$('<label>', {
								for: 'filter_fakultas',
								html: 'Fakultas',
							}),
							$('<select>', {
								autocomplete: 'off',
								type: 'text',
								id: 'filter_fakultas',
								name: 'filter_fakultas',
								class: 'form-control js-select2',
								html: $('<option>', {
									html: ''
								})
							})
						]
					})

					// Filter prodi
					let filter_prodi = $('<div>', {
						class: 'col-md-4',
						html: [
							$('<label>', {
								for: 'filter_prodi',
								html: 'Prodi',
							}),
							$('<select>', {
								autocomplete: 'off',
								type: 'text',
								id: 'filter_prodi',
								name: 'filter_prodi',
								class: 'form-control js-select2',
								disabled: true,
								html: $('<option>', {
									html: ''
								})
							})
						]
					})

					return $('<div>', {
						class: 'row',
						html: [filter_tanggal, filter_fakultas, filter_prodi]
					}).prop('outerHTML')
				})

				/**
				 * Keperluan filter menggunakan select2
				 */
				// ================================================== //
				$('#filter_fakultas').select2({
					placeholder: 'Pilih Fakultas',
					width: '100%',
					ajax: {
						url: BASE_URL + "ajax_get_fakultas",
						dataType: 'JSON',
						delay: 250,
						data: function(params) {
							return {
								search: params.term, // search term
								page: params.page || 1
							};
						},
						processResults: function(response, params) {
							let myResults = [];
							let results = response.data
							results.map(item => {
								myResults.push({
									'id': item.id,
									'text': item.nama
								});
							})
							return {
								results: myResults,
							};
						}
					}
				}).on('select2:select', function(event) {
					$(`#filter_prodi`).prop('disabled', false)
					datatable.column('nama_fakultas:name')
						.search(event.params.data.text)
						.draw()

					$(`#filter_prodi`).select2({
						placeholder: 'Pilih Program Studi',
						width: '100%',
						ajax: {
							url: BASE_URL + "ajax_get_prodi",
							dataType: 'JSON',
							delay: 250,
							data: function(params) {
								return {
									search: params.term, // search term
									fakultas_id: event.params.data.id,
									page: params.page || 1
								};
							},
							processResults: function(response, params) {
								let myResults = [];
								let results = response.data
								results.map(item => {
									myResults.push({
										'id': item.id,
										'fakultas_id': event
											.params.data.id,
										'text': item.nama
									});
								})

								return {
									results: myResults,
								};
							}
						}
					}).on('select2:select', function(event) {
						datatable.column('nama_prodi:name')
							.search(event.params.data.text)
							.draw()
					})
				})
				// ================================================== //

				$('.datepicker').datepicker({
					format: 'yyyy-mm-dd',
					endDate: 'now',
					clearBtn: true,
					todayBtn: 'linked',
					autoclose: true
				})

				bsCustomFileInput.init()
				$('[title]').tooltip()
			},
		})

		datatable.on('draw.dt', () => {
			let PageInfo = datatable.page.info();
			datatable.column(0, {
				page: 'current'
			}).nodes().each(function(cell, i) {
				cell.innerHTML = i + 1 + PageInfo.start;
			});
		});
		// socket.on('backend-reload_dt-mahasiswa', () => {
		//     initMap()
		//     datatable.ajax.reload();
		// })
		// ================================================== //

		/**
		 * Keperluan CRUD
		 */
		// ================================================== //

		$insert = async () => {
			Swal.fire({
				title: 'Form Tambah Data',
				width: '800px',
				icon: 'info',
				html: `<?= $this->load->view("contents/$uri_segment/components/form_tambah", '', true); ?>`,
				confirmButtonText: '<i class="fa fa-check-square-o"></i> Simpan Data',
				showCancelButton: true,
				focusConfirm: false,
				showLoaderOnConfirm: true,
				allowOutsideClick: false,
				allowEscapeKey: false,
				allowEnterKey: false,
				showCloseButton: true,
				reverseButtons: true,
				didOpen: () => {
					$('.swal2-actions').css('z-index', '0')
					select2_in_swal('tambah')
					map_in_swal('tambah')
					bsCustomFileInput.init()
				},
				preConfirm: async () => {
					let formData = new FormData(document.getElementById('form_tambah'));

					formData.append(
						await csrf().then(csrf => csrf.token_name),
						await csrf().then(csrf => csrf.hash)
					)

					$('#form_tambah .invalid-feedback').fadeOut(500)
					$('#form_tambah .is-invalid').removeClass('is-invalid')
					let response = await axios.post(BASE_URL + 'insert', formData)
						.then(res => res.data.message)
						.catch(err => {
							let errors = err.response.data.errors;
							if (typeof errors === 'object') {
								Object.entries(errors).map(([key, value]) => {
									$(`#input_tambah_${key}`).addClass('is-invalid')
									$(`#error_tambah_${key}`).html(value).fadeIn(500)
									$(`.select2-selection[aria-labelledby=select2-select_tambah_${key}-container]`).css('border-color', '#dc3545')
								})
							}
							Swal.showValidationMessage(err.response.data.message)
						})

					return {
						data: response
					}
				}
			}).then((result) => {
				if (result.value) {
					Swal.fire({
						title: 'Berhasil',
						icon: 'success',
						text: result.value.data,
						showConfirmButton: false,
						allowEscapeKey: false,
						allowOutsideClick: false,
						timer: 1500
					}).then(() => {
						bsCustomFileInput.destroy()
						datatable.ajax.reload()
					})
				}
			})
		}

		$update = async (element) => {
			let row = datatable.row($(element).closest('tr')).data();

			Swal.fire({
				title: 'Form Ubah Data',
				width: '800px',
				icon: 'info',
				html: `<?= $this->load->view("contents/$uri_segment/components/form_ubah", '', true); ?>`,
				confirmButtonText: '<i class="fa fa-check-square-o"></i> Simpan Data',
				showCancelButton: true,
				focusConfirm: false,
				showLoaderOnConfirm: true,
				allowOutsideClick: false,
				allowEscapeKey: false,
				allowEnterKey: false,
				showCloseButton: true,
				reverseButtons: true,
				didOpen: () => {
					$('.swal2-actions').css('z-index', '0')
					select2_in_swal('ubah')
					map_in_swal('ubah')

					$('#form_ubah input#input_ubah_nim[name=nim]').val(row.nim);
					$('#form_ubah input#input_ubah_nama[name=nama]').val(row.nama);
					$('#form_ubah input#input_ubah_angkatan[name=angkatan]').val(row.angkatan);
					$('#form_ubah input#input_ubah_latitude[name=latitude]').val(row.latitude);
					$('#form_ubah input#input_ubah_longitude[name=longitude]').val(row.longitude);

					$('#form_ubah select#select_ubah_fakultas_id')
						.append(new Option(row.nama_fakultas, row.fakultas_id, true, true))
						.trigger('change')
						.trigger({
							type: 'select2:select',
							params: {
								data: {
									id: row.fakultas_id,
									fakultas_id: row.fakultas_id,
									prodi_id: row.prodi_id
								}
							}
						})

					$('#form_ubah select#select_ubah_prodi_id')
						.append(new Option(row.nama_prodi, row.prodi_id, true, true))
						.trigger('change')
						.trigger({
							type: 'select2:select',
							params: {
								data: {
									fakultas_id: row.fakultas_id,
									prodi_id: row.prodi_id
								}
							}
						})

					bsCustomFileInput.init()
				},
				preConfirm: async () => {
					let formData = new FormData(document.getElementById('form_ubah'));

					formData.append(
						await csrf().then(csrf => csrf.token_name),
						await csrf().then(csrf => csrf.hash)
					)
					formData.append('id', row.id)
					formData.append('old_foto', row.foto)

					$('#form_tambah .invalid-feedback').fadeOut(500)
					$('#form_tambah .is-invalid').removeClass('is-invalid')
					let response = await axios.post(BASE_URL + 'update', formData)
						.then(res => res.data.message)
						.catch(err => {
							let errors = err.response.data.errors;
							if (typeof errors === 'object') {
								Object.entries(errors).map(([key, value]) => {
									$(`#input_ubah_${key}`).addClass('is-invalid')
									$(`#error_ubah_${key}`).html(value).fadeIn(500)
									$(`.select2-selection[aria-labelledby=select2-select_ubah_${key}-container]`).css('border-color', '#dc3545')
								})
							}
							Swal.showValidationMessage(err.response.data.message)
						})

					return {
						data: response
					}
				}
			}).then((result) => {
				if (result.value) {
					Swal.fire({
						title: 'Berhasil',
						icon: 'success',
						text: result.value.data,
						showConfirmButton: false,
						allowEscapeKey: false,
						allowOutsideClick: false,
						timer: 1500
					}).then(() => {
						bsCustomFileInput.destroy()
						datatable.ajax.reload()
					})
				}
			})
		}

		$delete = async (element) => {
			Swal.fire({
				title: 'Are you sure?',
				text: "You won't be able to revert this!",
				icon: 'warning',
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, delete it!',
				showCancelButton: true,
				showCancelButton: true,
				showLoaderOnConfirm: true,
				allowOutsideClick: false,
				allowEscapeKey: false,
				allowEnterKey: false,
				showCloseButton: true,
				reverseButtons: true,
			}).then(async (result) => {
				if (result.isConfirmed) {
					loading()

					let formData = new FormData();
					formData.append('id', $(element).data('id'));
					formData.append(
						await csrf().then(csrf => csrf.token_name),
						await csrf().then(csrf => csrf.hash)
					)

					axios.post(BASE_URL + 'delete', formData)
						.then(res => {
							Swal.fire({
								icon: 'success',
								title: 'Success!',
								text: res.data.message,
								showConfirmButton: false,
								timer: 1500
							})

							// socket.emit('backend-crud-mahasiswa', {})
							datatable.ajax.reload()
						}).catch(err => {
							console.error(err);
							Swal.fire({
								icon: 'error',
								title: err.response.statusText,
								html: err.response.data.message,
								// text: err.response.
							})
						})
				}
			})
		}

		$import_excel = async () => {
			Swal.fire({
				title: 'Form Import Excel',
				width: '800px',
				icon: 'info',
				html: `<?= $this->load->view("contents/$uri_segment/components/form_import", '', true); ?>`,
				confirmButtonText: '<i class="fa fa-check-square-o"></i> Import File',
				showCancelButton: true,
				focusConfirm: false,
				showLoaderOnConfirm: true,
				allowOutsideClick: false,
				allowEscapeKey: false,
				allowEnterKey: false,
				showCloseButton: true,
				reverseButtons: true,
				didOpen: () => {
					$('.swal2-actions').css('z-index', '0')
					bsCustomFileInput.init()

					$('#form_import #downloadTemplateExcel').click(async () => {
						// location.replace(BASE_URL + "download_template_excel")
						$('.tombol-download-template').hide()
						$('.loader').show()

						let formData = new FormData();

						formData.append(
							await csrf().then(csrf => csrf.token_name),
							await csrf().then(csrf => csrf.hash)
						)

						axios.post(BASE_URL + 'download_template_excel', formData, {
								responseType: 'blob'
							})
							.then(blob => {
								$('.tombol-download-template').show()
								$('.loader').hide()

								const url = window.URL.createObjectURL(new Blob([blob.data]));
								const a = document.createElement('a');
								a.style.display = 'none';
								a.href = url;
								a.download = 'template_excel.xlsx';
								document.body.appendChild(a);
								a.click();
								window.URL.revokeObjectURL(url);
							}).catch(err => {
								console.error(err);

							})
					})

				},
				preConfirm: async () => {
					let formData = new FormData(document.getElementById('form_import'));

					formData.append(
						await csrf().then(csrf => csrf.token_name),
						await csrf().then(csrf => csrf.hash)
					)

					let response = await axios.post(BASE_URL + 'import_excel', formData)
						.then(res => res.data.message)
						.catch(err => {
							let errors = err.response.data.errors;

							Swal.showValidationMessage(err.response.data.message)
						})

					return {
						data: response
					}
				}
			}).then((result) => {
				if (result.value) {
					Swal.fire({
						title: 'Berhasil',
						icon: 'success',
						text: result.value.data,
						showConfirmButton: false,
						allowEscapeKey: false,
						allowOutsideClick: false,
						timer: 1500
					}).then(() => {
						bsCustomFileInput.destroy()
						datatable.ajax.reload()
					})
				}
			})
		}

		$export_excel = async () => {
			loading()

			let formData = new FormData();
			formData.append(
				await csrf().then(csrf => csrf.token_name),
				await csrf().then(csrf => csrf.hash)
			)

			axios.post(BASE_URL + 'export_excel', formData, {
					responseType: 'blob'
				})
				.then(blob => {
					const url = window.URL.createObjectURL(new Blob([blob.data]));
					const a = document.createElement('a');
					a.style.display = 'none';
					a.href = url;
					a.download = 'export_excel.xlsx';
					document.body.appendChild(a);
					a.click();
					window.URL.revokeObjectURL(url);
					Swal.fire({
						icon: 'success',
						title: 'Success!',
						text: "Berhasil melakukan export",
						showConfirmButton: false,
						timer: 1500
					})
				}).catch(err => {
					console.error(err);
					Swal.fire({
						icon: 'error',
						title: err.response.statusText,
						html: err.response.data.message,
						// text: err.response.statusText,
					})
				})
		}

		$export_pdf = async () => {
			loading()

			let formData = new FormData();
			formData.append(
				await csrf().then(csrf => csrf.token_name),
				await csrf().then(csrf => csrf.hash)
			)

			axios.post(BASE_URL + 'export_pdf', formData, {
					responseType: 'blob'
				})
				.then(blob => {
					const url = window.URL.createObjectURL(new Blob([blob.data]));
					const a = document.createElement('a');
					a.style.display = 'none';
					a.href = url;
					a.download = 'export_pdf.pdf';
					document.body.appendChild(a);
					a.click();
					window.URL.revokeObjectURL(url);
					Swal.fire({
						icon: 'success',
						title: 'Success!',
						text: "Berhasil melakukan export",
						showConfirmButton: false,
						timer: 1500
					})
				}).catch(err => {
					console.error(err);
					Swal.fire({
						icon: 'error',
						title: err.response.statusText,
						html: err.response.data.message,
						// text: err.response.statusText,
					})
				})
		}

		$export_word = async () => {
			loading()

			let formData = new FormData();
			formData.append(
				await csrf().then(csrf => csrf.token_name),
				await csrf().then(csrf => csrf.hash)
			)

			axios.post(BASE_URL + 'export_docx', formData, {
					responseType: 'blob'
				})
				.then(blob => {
					const url = window.URL.createObjectURL(new Blob([blob.data]));
					const a = document.createElement('a');
					a.style.display = 'none';
					a.href = url;
					a.download = 'export_word.docx';
					document.body.appendChild(a);
					a.click();
					window.URL.revokeObjectURL(url);
					Swal.fire({
						icon: 'success',
						title: 'Success!',
						text: "Berhasil melakukan export",
						showConfirmButton: false,
						timer: 1500
					})
				}).catch(err => {
					console.error(err);
					Swal.fire({
						icon: 'error',
						title: err.response.statusText,
						html: err.response.data.message,
						// text: err.response.statusText,
					})
				})
		}
		// ================================================== //

		/**
		 * Keperluan input select2 didalam form
		 */
		// ================================================== //
		const select2_in_swal = (status) => {
			$(`#form_${status} select#select_${status}_fakultas_id`).select2({
				placeholder: 'Pilih Fakultas',
				width: '100%',
				dropdownParent: $(`#swal2-html-container`),
				ajax: {
					url: BASE_URL + 'ajax_get_fakultas',
					dataType: 'JSON',
					delay: 250,
					data: function(params) {
						return {
							search: params.term, // search term
						};
					},
					processResults: function(response) {
						let myResults = [];
						response.data.map(item => {
							myResults.push({
								'id': item.id,
								'text': item.nama
							});
						})
						return {
							results: myResults
						};
					}
				}
			}).on('select2:select', function(event) {
				$(`#form_${status} #select_${status}_prodi_id`).prop('disabled', false)
				$(`#form_${status} #select_${status}_prodi_id`).select2({
					placeholder: 'Pilih Program Studi',
					width: '100%',
					dropdownParent: $(`#swal2-html-container`),
					ajax: {
						url: BASE_URL + 'ajax_get_prodi',
						dataType: 'JSON',
						delay: 250,
						data: function(params) {
							return {
								search: params.term, // search term
								fakultas_id: event.params.data.id
							};
						},
						processResults: function(response) {
							let myResults = [];
							response.data.map(item => {
								myResults.push({
									'id': item.id,
									'text': item.nama
								});
							})
							return {
								results: myResults
							};
						}
					}
				})
			})
		}
		// ================================================== //
	})
</script>
