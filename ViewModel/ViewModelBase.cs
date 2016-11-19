using System;
using System.ComponentModel;
using System.Diagnostics;
using System.Globalization;
using System.Windows.Input;
using Windows.UI;
using Windows.UI.Text;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;

namespace Grappbox.ViewModel
{
    #region Converter

    public class DateTimeToDateConverter : IValueConverter
    {
        public object Convert(object value, Type targetType, object parameter, string language)
        {
            try
            {
                DateTime date = (DateTime)value;
                return new DateTimeOffset(date);
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                return DateTimeOffset.MinValue;
            }
        }

        public object ConvertBack(object value, Type targetType, object parameter, string language)
        {
            try
            {
                DateTimeOffset dto = (DateTimeOffset)value;
                return dto.DateTime;
            }
            catch (Exception ex)
            {
                return DateTime.MinValue;
            }
        }
    }

    public class CommandHandler : ICommand
    {
        public Action _action;
        public bool _canExecute;

        public event EventHandler CanExecuteChanged;

        public CommandHandler(Action action)
        {
            _action = action;
            _canExecute = false;
        }

        public bool CanExecute(object parameter)
        {
            if (_action == null)
                return false;
            else
                return true;
        }

        public void Execute(object parameter)
        {
            _action();
        }
    }

    public class CommandHandler<T> : ICommand
    {
        public Action<T> _action;
        public bool _canExecute;

        public event EventHandler CanExecuteChanged;

        public CommandHandler(Action<T> action)
        {
            _action = action;
            _canExecute = false;
        }

        public bool CanExecute(object parameter)
        {
            if (_action == null)
                return false;
            else
                return true;
        }

        public void Execute(object parameter)
        {
            T param = (T)parameter;
            _action.Invoke(param);
        }
    }

    #endregion Converter

    public abstract class ViewModelBase : INotifyPropertyChanged
    {
        public event PropertyChangedEventHandler PropertyChanged;

        public void NotifyPropertyChanged(string property)
        {
            if (PropertyChanged != null)
            {
                PropertyChanged(this, new PropertyChangedEventArgs(property));
            }
        }
    }
}