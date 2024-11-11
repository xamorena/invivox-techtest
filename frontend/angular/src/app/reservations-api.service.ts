import { Injectable } from '@angular/core';
import axios from 'axios';

const API_URL = "/api";
const HEADERS = {
  'Content-Type': 'application/json; charset=utf-8'
};

@Injectable({
  providedIn: 'root'
})
export class ReservationsApiService {

  public readonly apiUrl: string;
  public readonly headers: any;

  constructor() {
    this.apiUrl = API_URL;
    this.headers = HEADERS;
  }

  createReservation(reservation: any) {
      const data = JSON.stringify({
        date: new Date(Date.parse(reservation.date)).toISOString(),
        foodtruck: new String(reservation.foodtruck).trim().toUpperCase(),
      });
      return axios.post(`${this.apiUrl}/reservations`, data, {
          headers: this.headers,
        }).then( (response: any) => {
            console.log(response.data);
            return response.data;
        }).catch( (error: any) => {
            const msg = error.response.data?.detail;
            alert(msg);
            return msg;
        });
  }

  deleteReservation(reservationId: number | undefined) {
    if (reservationId) {
      return axios.delete(`${this.apiUrl}/reservations/${reservationId}`, {
        headers: this.headers
      }).then( (response: any) => {
        return true;
      }).catch( (error: any) => {
        return false;
      })
    }
    return false;
  }

  getReservations() {
    return axios.get(`${this.apiUrl}/reservations`, {
      headers: this.headers
    }).then( (response: any) => {
      const data = response.data.map( (item: any) => {
        return {
          id: item.id,
          date: new Date(item.date),
          foodtruck: item.foodtruck,
        };
      });
      return data;
    }).catch( (error: any) => {
      return [];
    })
  }

}
