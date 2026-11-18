<!-- Modal Ubah Password -->
<div class="modal fade" id="modalUbahPassword" tabindex="-1" aria-labelledby="modalUbahPasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modalUbahPasswordLabel">
                    <i class="icon-base bx bx-key me-2 text-primary"></i>Ubah Password Akun
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUbahPasswordNavbar">
                @csrf
                <div class="modal-body py-4">
                    <!-- Alert Error -->
                    <div class="alert alert-danger d-none form-errors" id="alertErrorUbahPassword" role="alert">
                        <ul class="mb-0 ps-3"></ul>
                    </div>

                    <div class="mb-3 form-password-toggle">
                        <label class="form-label" for="navbar_current_password">Password Saat Ini <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <input type="password" class="form-control" id="navbar_current_password" name="current_password"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                required />
                            <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                        </div>
                    </div>

                    <div class="mb-3 form-password-toggle">
                        <label class="form-label" for="navbar_new_password">Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <input type="password" class="form-control" id="navbar_new_password" name="new_password"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                required />
                            <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                        </div>
                        <small class="text-muted">Minimal 6 karakter.</small>
                    </div>

                    <div class="mb-3 form-password-toggle">
                        <label class="form-label" for="navbar_new_password_confirmation">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <input type="password" class="form-control" id="navbar_new_password_confirmation" name="new_password_confirmation"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                required />
                            <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitUbahPasswordNavbar">
                        <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                        <i class="icon-base bx bx-check me-1"></i> Simpan Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalUbahPassword');
    if (modalEl && modalEl.parentElement !== document.body) {
        document.body.appendChild(modalEl);
    }

    const $form = $('#formUbahPasswordNavbar');
    const $btn = $('#btnSubmitUbahPasswordNavbar');
    const $alert = $('#alertErrorUbahPassword');

    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function () {
            $form[0].reset();
            $alert.addClass('d-none').find('ul').empty();
            $form.find('.is-invalid').removeClass('is-invalid');
            if (window.Helpers && window.Helpers.initPasswordToggle) {
                window.Helpers.initPasswordToggle();
            }
        });
    }

    $form.on('submit', function (e) {
        e.preventDefault();
        $alert.addClass('d-none').find('ul').empty();
        $form.find('.is-invalid').removeClass('is-invalid');
        $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

        $.ajax({
            url: "{{ route('password.change') }}",
            type: "POST",
            data: $form.serialize(),
            success: function (res) {
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
                $form[0].reset();
                showNavbarToast(res.message || 'Password berhasil diperbarui!');
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    const $ul = $alert.find('ul');
                    $.each(errors, function (field, messages) {
                        $form.find(`[name="${field}"]`).addClass('is-invalid');
                        $.each(messages, function (i, msg) {
                            $ul.append(`<li>${msg}</li>`);
                        });
                    });
                    $alert.removeClass('d-none');
                } else {
                    const msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat mengubah password.';
                    $alert.find('ul').html(`<li>${msg}</li>`);
                    $alert.removeClass('d-none');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
            }
        });
    });

    function showNavbarToast(msg) {
        let $toast = $('#navbarGlobalToast');
        if (!$toast.length) {
            $('body').append(`
                <div id="navbarGlobalToast" class="bs-toast toast toast-placement-ex m-3 top-0 end-0 fade bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; z-index: 1090;">
                    <div class="toast-header bg-success text-white">
                        <i class="icon-base bx bx-check-circle me-2"></i>
                        <strong class="me-auto">Sukses</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body" id="navbarGlobalToastBody"></div>
                </div>
            `);
            $toast = $('#navbarGlobalToast');
        }
        $('#navbarGlobalToastBody').text(msg);
        const toastInst = new bootstrap.Toast($toast[0], { delay: 4000 });
        toastInst.show();
    }
});
</script>

