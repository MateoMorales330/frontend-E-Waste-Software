<template>
  <div class="view">
    <store-header></store-header>
    <banner-slider></banner-slider>
    <div class="quick-links">
      <button class="tile" @click="$router.push({name:'categoria'})">CATEGORIA<br>PRODUCTO</button>
      <button class="tile" @click="$router.push({name:'categoria'})">CATEGORIA<br>PRODUCTO</button>
      <button class="tile" @click="$router.push({name:'categoria'})">SERVICIO</button>
      <button class="tile" @click="$router.push({name:'categoria'})">SERVICIO</button>
    </div>
    <p v-if="cargando" class="producto-vacio">Cargando productos...</p>
    <p v-else-if="error" class="producto-error">{{ error }}</p>
    <div v-else class="products-wrap">
      <div class="product-row" v-for="(fila,indiceFila) in filasInicio" :key="indiceFila">
        <product-card v-for="producto in fila" :key="producto.id" :product="producto"></product-card>
      </div>
    </div>
    <div class="continua">(continua con ofertas etc)</div>
    <footer class="site-footer">
      DISCLAIMERS, CONTACTO, UBICACIÓN, ETC.
      <router-link class="admin-link" :to="{name:'admin-panel'}">Panel Administrador →</router-link>
    </footer>
  </div>
</template>

<script>
import StoreHeader from '../components/StoreHeader.vue';
import BannerSlider from '../components/BannerSlider.vue';
import ProductCard from '../components/ProductCard.vue';
import api from '../Api/api.js';
import { CATALOGO, dividirEnGrupos } from '../catalog.js';

export default {
  name: 'InicioView',
  components: { StoreHeader, BannerSlider, ProductCard },
  data() {
    return {
      productos: [],
      cargando: false,
      error: ''
    };
  },
  computed: {
    // Convierte los nombres del backend al formato que usa ProductCard.
    filasInicio() {
      const productosInicio = this.productos.slice(0, 9).map(producto => ({
        ...producto,
        // Acepta tanto los productos de Laravel como los productos antiguos.
        name: producto.nombre || producto.name,
        price: Number(producto.precio_venta ?? producto.price) || 0
      }));
      return dividirEnGrupos(productosInicio, 3);
    }
  },
  async mounted() {
    this.cargando = true;
    try {
      // Los productos creados o editados en el panel aparecen al recargar el inicio.
      const response = await api.get('/productos');
      // Conserva los productos de ejemplo mientras se completa el catálogo real.
      this.productos = [...response.data, ...CATALOGO];
    } catch (requestError) {
      console.error('Error al cargar productos del inicio:', requestError);
      // Si el backend está apagado, la portada sigue mostrando el catálogo temporal.
      this.productos = CATALOGO.map(producto => ({
        ...producto,
        nombre: producto.name,
        precio_venta: producto.price
      }));
    } finally {
      this.cargando = false;
    }
  }
};
</script>
