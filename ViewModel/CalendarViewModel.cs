using GrappBox.ApiCom;
using GrappBox.Model;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Input;
using Windows.UI.Xaml;

namespace GrappBox.ViewModel
{
    class CalendarViewModel : ViewModelBase
    {
        private int _currentYear;
        public int CurrentYear
        {
            get { return _currentYear; }
            set { _currentYear = value; NotifyPropertyChanged("CurrentYear"); }
        }
        private int _monthIndex;
        public int MonthIndex
        {
            get { return _monthIndex; }
            set
            {
                if (_monthIndex == 11 && value == 0)
                    CurrentYear = CurrentYear + 1;
                if (_monthIndex == 0 && value == 11)
                    CurrentYear = CurrentYear - 1;
                _monthIndex = value;
                NotifyPropertyChanged("MonthIndex");
                UpdateMonth();
            }
        }
        private int _weekIndex;
        public int WeekIndex
        {
            get { return _weekIndex; }
            set
            {
                if (_weekIndex == 3 && value == 0)
                    _monthIndex += 1;
                if (_weekIndex == 0 && value == 3)
                    _monthIndex -= 1;
                _weekIndex = value;
                NotifyPropertyChanged("WeekIndex");
            }
        }
        private Visibility _dayView;
        private Visibility _weekView;
        private Visibility _monthView;
        public Visibility DayView
        {
            get { return _dayView; }
            set
            {
                _dayView = value; NotifyPropertyChanged("DayView");
            }
        }
        public Visibility WeekView
        {
            get { return _weekView; }
            set
            {
                _weekView = value; NotifyPropertyChanged("WeekView");
            }
        }
        public Visibility MonthView
        {
            get { return _monthView; }
            set
            {
                _monthView = value; NotifyPropertyChanged("MonthView");
            }
        }
        private ObservableCollection<Model.Task> _tasks;
        private ObservableCollection<Event> _events;
        public ObservableCollection<Event> Events
        {
            get { return _events; }
            set { _events = value; NotifyPropertyChanged("Events"); }
        }
        public ObservableCollection<Model.Task> Tasks
        {
            get { return _tasks; }
            set { _tasks = value; NotifyPropertyChanged("Tasks"); }
        }
        private CalendarModel model;
        public CalendarModel.ViewType CurrentViewType
        {
            get { return model.Currentype; }
            set { model.Currentype = value; NotifyPropertyChanged("CurrentViewType"); }
        }
        private DateTime currentDateTime;

        public CalendarViewModel()
        {
            currentDateTime = new DateTime(DateTime.Now.Ticks);
            _currentYear = currentDateTime.Year;
            _monthIndex = currentDateTime.Month - 1;
            model = new CalendarModel();
            WeekView = Visibility.Collapsed;
            MonthView = Visibility.Visible;
            InitViewModel();
        }
        public async void InitViewModel()
        {
            Debug.WriteLine("currentDateTime: {0}", currentDateTime);
            _currentYear = currentDateTime.Year;
            Planning plan = await model.GetMonthPlanning(currentDateTime);
            Events = new ObservableCollection<Event>(plan.Events);
            Tasks = new ObservableCollection<Model.Task>(plan.Tasks);
            NotifyPropertyChanged("MonthIndex");
            NotifyPropertyChanged("CurrentYear");
        }
        public async void UpdateMonth()
        {
            currentDateTime = new DateTime(_currentYear, _monthIndex + 1, 1);

            Debug.WriteLine("currentDateTime: {0}", currentDateTime);
            Planning plan = await model.GetMonthPlanning(currentDateTime);
            Events = new ObservableCollection<Event>(plan.Events);
            Tasks = new ObservableCollection<Model.Task>(plan.Tasks);
        }
        private ICommand _viewTappedCommand;
        public ICommand ViewTappedCommand
        {
            get { return _viewTappedCommand ?? (_viewTappedCommand = new CommandHandler(ViewTappedAction)); }
        }
        public void ViewTappedAction()
        {
            if (CurrentViewType == CalendarModel.ViewType.MONTH)
            {
                CurrentViewType = CalendarModel.ViewType.WEEK;
                WeekView = Visibility.Visible;
                MonthView = Visibility.Collapsed;
            }
            else
            {
                CurrentViewType = CalendarModel.ViewType.MONTH;
                WeekView = Visibility.Collapsed;
                MonthView = Visibility.Visible;
            }
        }

        private ICommand _addEventCommand;
        public ICommand AddEventCommand
        {
            get { return _addEventCommand ?? (_addEventCommand = new CommandHandler(AddEventAction)); }
        }
        private DateModel DateTimeToDateModel(DateTime dt)
        {
            DateModel dm = new DateModel();
            dm.date = dt.ToString("yyyy-MM-dd HH:mm:ss");
            return dm;
        }
        public void AddEventAction()
        {
            EventPromptOpened = true;
        }

        #region EventCreation
        private bool _eventPromptOpened;
        private bool _eventPromptConfirmed;
        private string _eventPromptTitle;
        private string _eventPromptDescription;
        private DateTime _eventPromptBeginDate;
        private DateTime _eventPromptEndDate;
        public bool EventPromptOpened
        {
            get { return _eventPromptOpened; }
            set { _eventPromptOpened = value; NotifyPropertyChanged("EventPromptOpened"); }
        }
        public bool EventPromptConfirmed
        {
            get { return _eventPromptConfirmed; }
            set { _eventPromptConfirmed = value; NotifyPropertyChanged("EventPromptConfirmed");
                if (value == true)
                {
                    Event evt = new Event();
                    evt.Creator = new Creator();
                    evt.Creator.Fullname = User.GetUser().Firstname + " " + User.GetUser().Lastname;
                    evt.Creator.Id = User.GetUser().Id;
                    evt.CreatedAt = DateTimeToDateModel(DateTime.Now);
                    evt.BeginDate = DateTimeToDateModel(EventPromptBeginDate);
                    evt.EndDate = DateTimeToDateModel(EventPromptEndDate);
                    evt.Title = EventPromptTitle;
                    evt.Description = EventPromptDescription;
                    //Events.Add(evt);
                    //this.model.AddEvent(evt);
                }
            }
        }
        public string EventPromptTitle
        {
            get { return _eventPromptTitle; }
            set { _eventPromptTitle = value; NotifyPropertyChanged("EventPromptTitle"); }
        }
        public string EventPromptDescription
        {
            get { return _eventPromptDescription; }
            set { _eventPromptDescription = value; NotifyPropertyChanged("EventPromptDescription"); }
        }
        public DateTime EventPromptBeginDate
        {
            get { return _eventPromptBeginDate; }
            set { _eventPromptBeginDate = value; NotifyPropertyChanged("EventPromptBeginDate"); }
        }
        public DateTime EventPromptEndDate
        {
            get { return _eventPromptEndDate; }
            set { _eventPromptEndDate = value; NotifyPropertyChanged("EventPromptEndDate"); }
        }
        #endregion
    }
}
