<template>
    <section class="content">
        <div class="pull-right">
            <button
                class="btn btn-success btn-flat"
                :disabled="!order.isEditable"
                @click.prevent="saveVerify"
            >
                <i class="fa fa-save fa-fw" />Save Order
            </button>
            <div class="btn-group">
                <button
                    type="button"
                    class="btn btn-default dropdown-toggle dropdown btn-flat"
                    data-toggle="dropdown"
                >
                    <span class="fa fa-ellipsis-v" />
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li v-if="order.isDeletable">
                        <a
                            href="#"
                            @click.prevent="askDelete"
                        >
                            <i class="fa fa-trash fa-fw" />Delete Distribution
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <h3 class="box-title">
            Edit Partner Distribution
        </h3>

        <form role="form">
            <div class="row">
                <div class="col-md-4">
                    <ordermetadatabox
                        :order="order"
                        :statuses="statuses"
                        :editable="order.isEditable"
                        order-type="Partner Distribution Order"
                        :v="$v.order"
                    />
                </div>

                <div class="col-md-8">
                    <div class="box box-info">
                        <div
                            class="box-body"
                            :class="{ 'has-error': $v.order.partner.$error }"
                        >
                            <h3 class="box-title">
                                <i class="icon fa fa-sitemap fa-fw" />Partner
                            </h3>
                            <partnerselectionform
                                v-model="order.partner"
                                :editable="order.isEditable"
                                @change="$v.order.partner.$touch()"
                                @loaded="$v.order.partner.$reset()"
                            />
                            <fielderror v-if="$v.order.partner.$error">
                                Field is required
                            </fielderror>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div
                        class="box box-info"
                        :class="{ 'has-error': $v.order.lineItems.$error }"
                    >
                        <div class="box-header with-border">
                            <fielderror
                                v-if="$v.order.lineItems.$error"
                                classes="pull-right"
                            >
                                At least one line item must have a quantity
                            </fielderror>
                            <h3 class="box-title">
                                <i class="icon fa fa-list fa-fw" />Line Items
                            </h3>
                        </div>
                        <lineitemform
                            :products="products"
                            :line-items="order.lineItems"
                            :editable="order.isEditable"
                            :show-packs="true"
                        />
                    </div>
                </div>
            </div>
        </form>
        <modalinvalid />
        <modaldelete
            :action="this.deleteOrder"
            :order-title="order.title"
        />
        <modalcomplete
            :action="this.save"
            :order-title="order.title"
        />
    </section>
</template>


<script>
    import { required } from 'vuelidate/lib/validators';
    import { linesRequired, mod } from '../../../validators';
    import ModalOrderConfirmComplete from '../../../components/ModalOrderConfirmComplete.vue';
    import ModalOrderConfirmDelete from '../../../components/ModalOrderConfirmDelete.vue';
    import ModalOrderInvalid from '../../../components/ModalOrderInvalid.vue';
    import FieldError from '../../../components/FieldError.vue';
    import OrderMetadataBox from '../../../components/OrderMetadataBox.vue';
    import LineItemForm from '../../../components/LineItemForm.vue';
    import PartnerSelectionForm from '../../../components/PartnerSelectionForm.vue';
    export default {
        components: {
            'modalcomplete' : ModalOrderConfirmComplete,
            'modaldelete' : ModalOrderConfirmDelete,
            'modalinvalid' : ModalOrderInvalid,
            'fielderror' : FieldError,
            'ordermetadatabox' : OrderMetadataBox,
            'lineitemform' : LineItemForm,
            'partnerselectionform' : PartnerSelectionForm
        },
        props: ['new'],
        data() {
            return {
                order: {
                    lineItems: [],
                    partner: { id: null },
                    isEditable: true,
                    isDeletable: false,
                    status: 'COMPLETED',
                    reason: '',
                },
                products: [],
                statuses: [
                    {id: "PENDING", name: "Pending"},
                    {id: "COMPLETED", name: "Completed", commit: true },
                ]
            };
        },
        validations: {
            order: {
                partner: {
                    id: {
                        required
                    }
                },
                status: {
                    required
                },
                lineItems: { linesRequired }
            }
        },
        computed: {
            statusIsCompleted: function () {
                var self = this;
                var status = this.statuses.filter(function(item) {
                    return self.order.status == item.id
                });
                return status[0].commit === true;
            }
        },
        created() {
            var self = this;
            axios
                .get('/api/products', {params: { partnerOrderable: 1}})
                .then(response => this.products = response.data.data);
            if (this.new) {
            } else {
                axios
                    .get('/api/orders/distribution/' + this.$route.params.id, {
                        params: { include: ['lineItems', 'lineItems.product', 'lineItems.transactions', 'partner.addresses']}
                    })
                    .then(response => self.order = response.data.data);
            }
            console.log('Component mounted.')
        },
        methods: {
            saveVerify: function () {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    $('#invalidModal').modal('show');
                    return false;
                }
                if (this.statusIsCompleted) {
                    $('#confirmCommitModal').modal('show');
                } else {
                    this.save();
                }
            },
            save: function () {
                var self = this;
                if (this.new) {
                    axios
                        .post('/api/orders/distribution', this.order)
                        .then(response => self.$router.push('/orders/distribution'))
                        .catch(function (error) {
                            console.log(error);
                        });
                } else {
                    axios
                        .patch('/api/orders/distribution/' + this.$route.params.id, this.order)
                        .then(response => self.$router.push('/orders/distribution'))
                        .catch(function (error) {
                            console.log(error);
                        });
                }
            },
            askDelete: function() {
                $('#confirmModal').modal('show');
            },
            deleteOrder: function() {
                var self = this;
                axios
                    .delete('/api/orders/distribution/' + this.$route.params.id)
                    .then(self.$router.push('/orders/distribution'));
            }
        }
    }
</script>
