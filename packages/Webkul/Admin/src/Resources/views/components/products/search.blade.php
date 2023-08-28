<v-product-search {{ $attributes }}></v-product-search>

@pushOnce('scripts')
    <script type="text/x-template" id="v-product-search-template">
        <!-- Search Drawer -->
        <x-admin::drawer
            ref="searchProductDrawer"
            @close="searchTerm = ''; searchedProducts = [];"
        >
            <!-- Drawer Header -->
            <x-slot:header>
                <div class="grid gap-[12px]">
                    <div class="flex justify-between items-center">
                        <p class="text-[20px] font-medium">
                            @lang('admin::app.components.products.search.title')
                        </p>

                        <div
                            class="mr-[45px] px-[12px] py-[6px] bg-blue-600 border border-blue-700 rounded-[6px] text-gray-50 font-semibold cursor-pointer"
                            @click="addSelected"
                        >
                            @lang('admin::app.components.products.search.add-btn')
                        </div>
                    </div>

                    <div class="relative w-full">
                        <input
                            type="text"
                            class="bg-white border border-gray-300 rounded-lg block w-full pl-[12px] pr-[40px] py-[5px] leading-6 text-gray-600 transition-all hover:border-gray-400"
                            placeholder="Search by name"
                            v-model="searchTerm"
                            @input="search"
                        />

                        <span class="icon-search text-[22px] absolute right-[12px] top-[6px] flex items-center pointer-events-none"></span>
                    </div>
                </div>
            </x-slot:header>

            <!-- Drawer Content -->
            <x-slot:content class="!p-0">
                <div
                    class="grid"
                    v-if="filteredSearchedProducts.length"
                >
                    <div
                        class="flex gap-[10px] justify-between px-[16px] py-[24px] border-b-[1px] border-slate-300"
                        v-for="product in filteredSearchedProducts"
                    >
                        <!-- Information -->
                        <div class="flex gap-[10px]">
                            <!-- Checkbox -->
                            <div class="">
                                <input
                                    type="checkbox"
                                    class="sr-only peer"
                                    :id="'searched-product' + product.id"
                                    v-model="product.selected"
                                />

                                <label
                                    class="icon-uncheckbox text-[24px] peer-checked:icon-checked peer-checked:text-blue-600 cursor-pointer"
                                    :for="'searched-product' + product.id"
                                >
                                </label>
                            </div>

                            <!-- Image -->
                            <div
                                class="w-full h-[60px] max-w-[60px] max-h-[60px] relative rounded-[4px] overflow-hidden"
                                :class="{'border border-dashed border-gray-300': ! product.images.length}"
                            >
                                <template v-if="! product.images.length">
                                    <img src="{{ bagisto_asset('images/product-placeholders/front.svg') }}">
                                
                                    <p class="w-full absolute bottom-[5px] text-[6px] text-gray-400 text-center font-semibold">Product Image</p>
                                </template>

                                <template v-else>
                                    <img :src="product.images[0].url">
                                </template>
                            </div>

                            <!-- Details -->
                            <div class="grid gap-[6px] place-content-start">
                                <p class="text-[16x] text-gray-800 font-semibold">
                                    @{{ product.name }}
                                </p>

                                <p class="text-gray-600">
                                    @{{ "@lang('admin::app.components.products.search.sku')".replace(':sku', product.sku) }}
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="grid gap-[4px] place-content-start text-right">
                            <p class="text-gray-800 font-semibold">
                                @{{ $admin.formatPrice(product.price) }}
                            </p>

                            <p class="text-green-600">
                                @{{ "@lang('admin::app.components.products.search.qty')".replace(':qty', totalQty(product)) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- For Empty Variations -->
                <div
                    class="grid gap-[14px] justify-center justify-items-center py-[40px] px-[10px]"
                    v-else
                >
                    <!-- Placeholder Image -->
                    <img
                        src="{{ bagisto_asset('images/icon-add-product.svg') }}"
                        class="w-[80px] h-[80px] border border-dashed border-gray-300 rounded-[4px]"
                    />

                    <!-- Add Variants Information -->
                    <div class="flex flex-col items-center">
                        <p class="text-[16px] text-gray-400 font-semibold">
                            @lang('admin::app.components.products.search.empty-title')
                        </p>

                        <p class="text-gray-400">
                            @lang('admin::app.components.products.search.empty-info')
                        </p>
                    </div>
                </div>
            </x-slot:content>
        </x-admin::drawer>
    </script>

    <script type="module">
        app.component('v-product-search', {
            template: '#v-product-search-template',

            props: {
                addedProductIds: {
                    type: Array,
                    default: []                    
                },

                queryParams: {
                    type: Object,
                    default: () => ({})
                },
            },

            data() {
                return {
                    searchTerm: '',

                    searchedProducts: [],
                }
            },

            computed: {
                filteredSearchedProducts() {
                    return this.searchedProducts.filter(product => ! this.addedProductIds.includes(product.id));
                }
            },

            methods: {
                openDrawer() {
                    this.$refs.searchProductDrawer.open();
                },

                search() {
                    if (! this.searchTerm.length) {
                        this.searchedProducts = [];

                        return;
                    }

                    let self = this;
                    
                    this.$axios.get("{{ route('admin.catalog.products.search') }}", {
                            params: {
                                ...{query: this.searchTerm},
                                ...this.queryParams
                            }
                        })
                        .then(function(response) {
                            self.searchedProducts = response.data.data;
                        })
                        .catch(function (error) {
                        })
                },

                addSelected() {
                    let selectedProducts = this.searchedProducts.filter(product => product.selected);

                    this.$emit('onProductAdded', selectedProducts);

                    this.$refs.searchProductDrawer.close();
                },

                totalQty(product) {
                    let qty = 0;

                    product.inventories.forEach(function (inventory) {
                        qty += inventory.qty;
                    });

                    return qty;
                }
            }
        });
    </script>
@endPushOnce