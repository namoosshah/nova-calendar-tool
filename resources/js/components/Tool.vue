<template>
  <div>
    <div class="card py-6 px-6">
      <FullCalendar ref="fullCalendar" :options="calendarOptions"/>
    </div>

    <transition name="fade">
      <EventModal
          v-if="showModal"
          :currentEvent="currentEvent"
          :currentDate="currentDate"
          :doctors="doctors"
          :patients="patients"
          :live-sessions="liveSessions"
          @refreshEvents="refreshEvents"
          @close="closeModal"
          @confirm="saveEvent"
          @delete="deleteEvent"
          @getPatientSessions="getPatientSessions"
      />
    </transition>
  </div>
</template>

<script>
import FullCalendar from '@fullcalendar/vue';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import allLocales from '@fullcalendar/core/locales-all';
import EventModal from './EventModal';

export default {
  components: {
    FullCalendar,
    EventModal
  },
  data() {
    return {
      calendarOptions: {
        events: '/nova-vendor/nova-calendar-tool/events',
        eventContent: function(arg) {
          let divEl = document.createElement('div')
          divEl.innerText  = arg.event.title.replace(/"/g,"");
          return { domNodes: [ divEl ] };
        },
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        initialView: 'dayGridMonth',
        nowIndicator: true,
        editable: false,
        nextDayThreshold: '00:00:00',
        fixedWeekCount: false,
        displayEventTime: false,
        locale: Nova.config.fullcalendar_locale || 'en',
        dateClick: this.handleDateClick,
        eventClick: this.handleEventClick,
        eventTimeFormat: {
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit',
          hour12: false
        }
      },
      currentEvent: null,
      currentDate: null,
      doctors: [],
      patients: [],
      liveSessions: [],
      showModal: false
    }
  },
  methods: {
    handleDateClick(date) {
      this.showModal = true;
      this.currentDate = date;
    },
    handleEventClick(event) {
      this.showModal = true;
      this.currentEvent = event;
      if (event && event.event.extendedProps.patient_id !== null) {
        this.getPatientSessions(event.event.extendedProps.patient_id);
      }
    },
    closeModal() {
      this.showModal = false;
      this.currentEvent = null;
      this.currentDate = null;
    },
    refreshEvents() {
      this.$refs.fullCalendar.getApi().refetchEvents();
    },
    getDoctors() {
      Nova.request()
          .get('/nova-vendor/nova-calendar-tool/doctors')
          .then(response => {
            if (response.data) {
              this.doctors = response.data.data;
            }
          }).catch(response => this.$toasted.show('Something went wrong', {type: 'error'}));
    },
    getPatients() {
      Nova.request()
          .get('/nova-vendor/nova-calendar-tool/patients')
          .then(response => {
            if (response.data) {
              this.patients = response.data.data;
            }
          }).catch(response => this.$toasted.show('Something went wrong', {type: 'error'}));
    },
    getPatientSessions(patient_id) {
      Nova.request()
          .get('/nova-vendor/nova-calendar-tool/live-sessions/' + patient_id)
          .then(response => {
            if (response.data) {
              this.liveSessions = response.data.data;
            }
          }).catch(response => this.$toasted.show('Something went wrong', {type: 'error'}));
    }
  },
  mounted() {
    this.getDoctors();
    this.getPatients();
  }
}
</script>

<style>
</style>