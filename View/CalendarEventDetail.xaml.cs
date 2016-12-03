using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Grappbox.Model;
using Grappbox.ViewModel;

// Pour plus d'informations sur le modèle d'élément Page vierge, voir la page http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class CalendarEventDetail : Page
    {
        public EventViewModel Event { get; private set; }
        public CalendarEventDetail()
        {
            this.InitializeComponent();
        }

        protected override void OnNavigatedTo(NavigationEventArgs e)
        {
            base.OnNavigatedTo(e);
            if (e.Parameter == null)
                return;
            var evt = e.Parameter as EventViewModel;
            Event = evt;
            DateTimeOffset begin = evt.BeginDateTime;
            DateTimeOffset end = evt.EndDateTime;
            BeginDatePicker.Date = begin;
            EndDatePicker.Date = end;
            BeginTimePicker.Time = begin.TimeOfDay;
            EndTimePicker.Time = end.AddHours(1).TimeOfDay;
        }

        private void TimePicker_TimeChanged(object sender, TimePickerValueChangedEventArgs e)
        {
            DateTimeOffset offset = new DateTimeOffset(Event.EndDateTime.Date.Year, Event.EndDateTime.Date.Month, Event.EndDateTime.Date.Day, e.NewTime.Hours,
                e.NewTime.Minutes, e.NewTime.Seconds, new TimeSpan(1, 0, 0));
            Event.EndDate = offset.ToString("yyyy-MM-dd HH:mm:ss");
        }

        private void TimePicker_TimeChanged_1(object sender, TimePickerValueChangedEventArgs e)
        {
            DateTimeOffset offset = new DateTimeOffset(Event.EndDateTime.Date.Year, Event.EndDateTime.Date.Month, Event.EndDateTime.Date.Day, e.NewTime.Hours,
                e.NewTime.Minutes, e.NewTime.Seconds, new TimeSpan(1, 0, 0));
            Event.BeginDate = offset.ToString("yyyy-MM-dd HH:mm:ss");
        }

        private void EndDatePicker_DateChanged(CalendarDatePicker sender, CalendarDatePickerDateChangedEventArgs args)
        {
            DateTimeOffset offset = new DateTimeOffset(args.NewDate.Value.Date);
            Event.EndDate = offset.ToString("yyyy-MM-dd HH:mm:ss");
        }

        private void BeginDatePicker_DateChanged(CalendarDatePicker sender, CalendarDatePickerDateChangedEventArgs args)
        {
            DateTimeOffset offset = new DateTimeOffset(args.NewDate.Value.Date);
            Event.BeginDate = offset.ToString("yyyy-MM-dd HH:mm:ss");
        }
    }
}
