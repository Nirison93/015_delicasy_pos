<!-- <template>
  <footer class="w-full border-t border-orange-200 bg-orange-50 py-2">
    <p class="text-center text-[14px] text-orange-400 tracking-wide">
      Copyright &copy; {{ currentYear }} &nbsp;|&nbsp; Design &amp; Developed by
      <span class="font-semibold text-orange-600">Onemaxweb Solution</span>
    </p>
  </footer>
</template> -->

<script setup>
import { ref } from 'vue';

// Get the current year
const currentYear = ref(new Date().getFullYear());
</script>
