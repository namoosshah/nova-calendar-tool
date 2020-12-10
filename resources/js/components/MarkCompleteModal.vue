<template>
  <modal tabindex="-1" role="dialog" @modal-close="handleClose">
    <form @keydown="handleKeydown" @submit.prevent.stop="handleConfirm"
          class="bg-white rounded-lg shadow-lg overflow-hidden w-action">
      <div>
        <heading :level="2" class="border-b border-40 py-8 px-8">Confirm action</heading>
        <p class="text-80 px-8 my-8"> Are you sure you want to mark this event completed? </p>
      </div>
      <div class="bg-30 px-6 py-3 flex">
        <div class="flex items-center ml-auto">
          <button type="button" @click.prevent="handleClose"
                  class="btn btn-link dim cursor-pointer text-80 ml-auto mr-6">
            Cancel
          </button>
          <button :disabled="working" type="submit" class="btn btn-default btn-primary">
            <span v-else>Confirm</span>
          </button>
        </div>
      </div>
    </form>
  </modal>
</template>

<script>
export default {
  name: "MarkCompleteModal",
  props: ['currentEvent'],
  data() {
    return {
      working: false
    }
  },
  methods: {
    handleConfirm() {
      this.working = true
      Nova.request()
          .put('/nova-vendor/nova-calendar-tool/events/' + this.currentEvent.event.id + '/mark/completed')
          .then(response => {
            if (response.data.success) {
              this.$toasted.show('Event has been marked completed', {type: 'success'});
              this.$emit('close');
              this.$emit('refreshEvents');
            }
          })
          .catch(response => this.$toasted.show('Something went wrong', {type: 'error'}));
    },
    handleClose() {
      this.$emit('close');
    },
  }
}
</script>

<style scoped>

</style>