using GrappBox.Ressources;
using System;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;

namespace GrappBox.CustomControler
{
    public sealed partial class EventPlanPrompt : UserControl
    {
        public static readonly DependencyProperty EventConfirmedProperty =
            DependencyProperty.Register("IsEventConfirmed", typeof(bool), typeof(EventPlanPrompt), null);
        public static readonly DependencyProperty CurrentDateTimeProperty =
            DependencyProperty.Register("CurrentDateTime", typeof(MyDateTime), typeof(EventPlanPrompt), null);
        public static readonly DependencyProperty IsOpenedProperty =
            DependencyProperty.Register("IsOpened", typeof(bool), typeof(EventPlanPrompt), null);

        public string Title
        {
            get { return title.Text; }
        }
        public string Description
        {
            get { return description.Text; }
        }

        public bool IsEventConfirmed
        {
            get { return (bool)GetValue(EventConfirmedProperty); }
            set { SetValue(EventConfirmedProperty, value); }
        }
        public bool IsOpened
        {
            get { return (bool)GetValue(IsOpenedProperty); }
            set { SetValue(IsOpenedProperty, value); }
        }
        public DateTime BeginDate
        {
            get { return new DateTime(beginDate.Date.Year, beginDate.Date.Month, beginDate.Date.Day, beginHour.Time.Hours, beginHour.Time.Minutes, 0); ; }
        }
        public DateTime EndDate
        {
            get { return new DateTime(endDate.Date.Year, endDate.Date.Month, endDate.Date.Day, endHour.Time.Hours, endHour.Time.Minutes, 0); }
        }

        private void ConfirmText_Click(object sender, RoutedEventArgs e)
        {
            IsEventConfirmed = true;
            IsOpened = false;
        }
        public void Clear()
        {
            title.Text = "";
            description.Text = "";
            beginDate.Date = DateTime.Now;
            endDate.Date = DateTime.Now;
            beginHour.Time = DateTime.Now.TimeOfDay;
            endHour.Time = DateTime.Now.TimeOfDay.Add(new TimeSpan(1,0,0));
        }
        private void CancelText_Click(object sender, RoutedEventArgs e)
        {
            IsEventConfirmed = false;
            IsOpened = false;
            title.Text = "";
            description.Text = "";
        }
        public EventPlanPrompt()
        {
            IsEventConfirmed = false;
            IsOpened = false;
            this.InitializeComponent();
        }

        private void description_TextChanged(object sender, TextChangedEventArgs e)
        {
            if (description.Text.Length != 0 && title.Text.Length != 0)
            {
                ConfirmText.IsEnabled = true;
            }
        }
    }
}
