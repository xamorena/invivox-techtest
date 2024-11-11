import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { ReservationsApiService } from './reservations-api.service';
import { FormsModule } from "@angular/forms";
import { MatButtonModule } from '@angular/material/button';
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';

interface ReservationForm {
  date: Date
  foodtruck: string
}

interface Reservation extends ReservationForm {
  id?: number
}

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, FormsModule, MatButtonModule, MatInputModule, MatFormFieldModule],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent {
  reservations: Array<Reservation> = [];
  reservation: ReservationForm = {
    date: new Date(),
    foodtruck: "Foodtruck",
  };
  showNewForm = false;
  currentDate = new Date();
  currentWeek = 1;
  weekdays = [0, 1, 2, 3, 4, 5, 6];

  constructor(private reservationsApiService: ReservationsApiService) {
    this.currentWeek = this.getWeek(this.currentDate);
  }

  getApiUrl() {
    return this.reservationsApiService.apiUrl;
  }

  getWeek(date: Date) {
    const firstDayOfYear = new Date(date.getFullYear(), 0, 1);
    const today = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const dayOfYear = (today.getTime() - firstDayOfYear.getTime() + 1) / 86400000;
    const week = Math.ceil(dayOfYear / 7);
    return week;
  }

  setPrevWeek() {
    this.currentDate = new Date(this.currentDate.getTime() - 7 * 24 * 60 * 60 * 1000);
    this.currentWeek = this.getWeek(this.currentDate);
  }

  setNextWeek() {
    this.currentDate = new Date(this.currentDate.getTime() + 7 * 24 * 60 * 60 * 1000);
    this.currentWeek = this.getWeek(this.currentDate);
  }

  ngOnInit() {
    this.getReservations()
    .then( (response: any) => console.log("Ready"))
    .catch( (error: any) => console.log("Error:", error))
  }

  getCurrentWeekdays() {
    return ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
  }

  newReservation() {
    this.showNewForm = true;
  }

  cancelReservation() {
    this.showNewForm = false;
  }

  async getReservations() {
    this.reservations = await this.reservationsApiService.getReservations();
  }

  async removeReservation(reservationId: number | undefined) {
    this.reservationsApiService.deleteReservation(reservationId);
    await this.getReservations();
    window.location.reload();
  }

  submitReservation() {
    this.reservationsApiService.createReservation(this.reservation)
    .then( async (data: any) => {
      this.reservation.date = new Date();
      this.reservation.foodtruck = "Foodtruck Name";
      this.showNewForm = false;
      await this.getReservations()
    })
  }
}
