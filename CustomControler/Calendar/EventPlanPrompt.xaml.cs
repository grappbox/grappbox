using System;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;

namespace GrappBox.CustomControler
{
    public sealed partial class EventPlanPrompt : UserControl
    {
        public static readonly DependencyProperty EventConfirmedProperty =
            DependencyProperty.Register("IsEventConfirmed", typeof(bool), typeof(EventPlanPrompt), null);
        public static readonly DependencyProperty TitleProperty =
            DependencyProperty.Register("Title", typeof(string), typeof(EventPlanPrompt), null);
        public static readonly DependencyProperty DescriptionProperty =
            DependencyProperty.Register("Description", typeof(string), typeof(EventPlanPrompt), null);
        public static readonly DependencyProperty IsOpenedProperty =
            DependencyProperty.Register("IsOpened", typeof(bool), typeof(EventPlanPrompt), null);
        public static readonly DependencyProperty BeginDateProperty =
            DependencyProperty.Register("BeginDate", typeof(DateTime), typeof(EventPlanPrompt), null);
        public static readonly DependencyProperty EndDateProperty =
            DependencyProperty.Register("EndDate", typeof(DateTime), typeof(EventPlanPrompt), null);

        public string Title
        {
            get { return (string)GetValue(TitleProperty); }
            set { SetValue(TitleProperty, value); }
        }
        public string Description
        {
            get { return (string)GetValue(DescriptionProperty); }
            set { SetValue(DescriptionProperty, value); }
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
            get { return (DateTime)GetValue(BeginDateProperty); }
            set { SetValue(BeginDateProperty, value); }
        }
        public DateTime EndDate
        {
            get { return (DateTime)GetValue(EndDateProperty); }
            set { SetValue(EndDateProperty, value); }
        }

        private void ConfirmText_Click(object sender, RoutedEventArgs e)
        {
            Title = title.Text;
            title.Text = "";
            Description = description.Text;
            description.Text = "";
            BeginDate = new DateTime(beginDate.Date.Year, beginDate.Date.Month, beginDate.Date.Day, beginHour.Time.Hours, beginHour.Time.Minutes, 0);
            EndDate = new DateTime(endDate.Date.Year, endDate.Date.Month, endDate.Date.Day, endHour.Time.Hours, endHour.Time.Minutes, 0);
            IsEventConfirmed = true;
            IsEventConfirmed = false;
            IsOpened = false;
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
