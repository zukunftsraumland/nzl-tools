<template>
  <div class="notifications-container">
    <transition-group name="notification">
      <div 
        v-for="notification in notifications" 
        :key="notification.id" 
        class="notification"
        :class="'notification-' + notification.type"
      >
        <div class="notification-content">
          {{ notification.message }}
        </div>
        <button class="notification-close" @click="removeNotification(notification.id)">
          &times;
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  computed: {
    ...mapGetters({
      notifications: 'notifications/all'
    })
  },
  methods: {
    removeNotification(id) {
      this.$store.dispatch('notifications/remove', id);
    }
  }
};
</script>

<style scoped>
.notifications-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  width: 300px;
}

.notification {
  margin-bottom: 10px;
  padding: 15px;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  justify-content: space-between;
  animation: slide-in 0.3s ease-out forwards;
}

.notification-content {
  flex: 1;
}

.notification-close {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  margin-left: 10px;
  opacity: 0.7;
}

.notification-close:hover {
  opacity: 1;
}

.notification-success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.notification-error {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.notification-warning {
  background-color: #fff3cd;
  color: #856404;
  border: 1px solid #ffeeba;
}

.notification-info {
  background-color: #d1ecf1;
  color: #0c5460;
  border: 1px solid #bee5eb;
}

.notification-enter-active, 
.notification-leave-active {
  transition: all 0.3s;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

@keyframes slide-in {
  0% {
    opacity: 0;
    transform: translateX(30px);
  }
  100% {
    opacity: 1;
    transform: translateX(0);
  }
}
</style> 