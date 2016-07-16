using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Input;
using Windows.UI.Xaml;
using Microsoft.Xaml.Interactions.Core;
using Microsoft.Xaml.Interactivity;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.Foundation;
using System.Diagnostics;
using Windows.UI.Xaml.Media;
using GrappBox.CustomControler;
using Windows.UI.Text;
using System.IO;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media.Imaging;
using GrappBox.Model;
using Windows.UI;
using System.Globalization;

namespace GrappBox.ViewModel
{
    public enum MenuEnum
    {
        DASHBOARD,
        WHITEBOARD_LIST,
        USER_SETTINGS,
        PROJECT_SETTING,
        CALENDAR,
        GANTT,
        CLOUD,
        TIMELINE,
        BUGTRACKER
    }
    #region Converter
    public class DateTimeToStringConverter : IValueConverter
    {
        public object Convert(object value, Type targetType, object parameter, string language)
        {
            try
            {
                DateModel dm = (DateModel)value;
                DateTime date = dm;
                return date.ToString(CultureInfo.CurrentCulture);
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                return "No date";
            }
        }

        public object ConvertBack(object value, Type targetType, object parameter, string language)
        {
            return null;
        }
    }
    public class OccupationColorConverter : IValueConverter
    {
        public object Convert(object value, Type targetType, object parameter, string language)
        {
            string occup = (string)value;
            if (occup == "free")
                return new SolidColorBrush(Colors.Green);
            else
                return new SolidColorBrush(Colors.Red);
        }

        public object ConvertBack(object value, Type targetType, object parameter, string language)
        {
            return null;
        }
    }
    public class IntegerToStringConverter : IValueConverter
    {
        public object Convert(object value, Type targetType, object parameter, string language)
        {
            int integer = (int)value;
            return integer;
        }

        public object ConvertBack(object value, Type targetType, object parameter, string language)
        {
            return null;
        }
    }
    public class DateModelToStringConverter : IValueConverter
    {
        public object Convert(object value, Type targetType, object parameter, string language)
        {
            if (value != null)
            {
                DateModel dm = (DateModel)value;
                String date = dm.date.Split(' ')[0];
                String hour = dm.date.Split(' ')[1];
                hour = hour.Remove(hour.LastIndexOf(':'));
                String final = "On " + date + " At " + hour;
                return final;
            }
            return null;
        }

        public object ConvertBack(object value, Type targetType, object parameter, string language)
        {
            return null;
        }
    }
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
    public class SenderParameterConverter : IValueConverter
    {
        public object Convert(object value, Type targetType,
                              object parameter, string language)
        {
            return parameter;
        }

        public object ConvertBack(object value, Type targetType,
                                  object parameter, string language)
        {
            throw new NotImplementedException();
        }
    }
    public class BoolToFontWeightConverter : IValueConverter
    {
        public object Convert(object value, Type targetType,
                              object parameter, string language)
        {
            return ((bool)value) ? FontWeights.Bold : FontWeights.Normal;
        }

        public object ConvertBack(object value, Type targetType,
                                  object parameter, string language)
        {
            throw new NotImplementedException();
        }
    }
    public class BoolToFontStyleConverter : IValueConverter
    {
        public object Convert(object value, Type targetType,
                              object parameter, string language)
        {
            return ((bool)value) ? FontStyle.Italic : FontStyle.Normal;
        }

        public object ConvertBack(object value, Type targetType,
                                  object parameter, string language)
        {
            throw new NotImplementedException();
        }
    }
    public class TappedPositionConverter : IValueConverter
    {
        public object Convert(
        object value,
        Type targetType,
        object parameter,
        string language)
        {
            var args = (TappedRoutedEventArgs)value;
            var element = (FrameworkElement)parameter;

            return args.GetPosition(element);
        }

        public object ConvertBack(
            object value,
            Type targetType,
            object parameter,
            string language)
        {
            throw new NotImplementedException();
        }
    }
    public class DoubleInputConverter : IValueConverter
    {
        public object Convert(
        object value,
        Type targetType,
        object parameter,
        string language)
        {
            var elem = (BrushPan)parameter;
            return elem.SelectedThickness;
        }

        public object ConvertBack(
            object value,
            Type targetType,
            object parameter,
            string language)
        {
            throw new NotImplementedException();
        }
    }
    public class BrushTappedConverter : IValueConverter
    {
        public object Convert(
        object value,
        Type targetType,
        object parameter,
        string language)
        {
            var element = (Colorpan)parameter;

            return element.SelectedColor;
        }

        public object ConvertBack(
            object value,
            Type targetType,
            object parameter,
            string language)
        {
            throw new NotImplementedException();
        }
    }
    public class NumberInverter : IValueConverter
    {
        public object Convert(
        object value,
        Type targetType,
        object parameter,
        string language)
        {
            if (value.GetType() == typeof(double))
                value = (double)(value) * -1.0;
            if (value.GetType() == typeof(int))
                value = (int)(value) * -1;
            return value;
        }

        public object ConvertBack(
            object value,
            Type targetType,
            object parameter,
            string language)
        {
            if (value.GetType() == typeof(double))
                value = (double)(value) * -1.0;
            if (value.GetType() == typeof(int))
                value = (int)(value) * -1;
            return value;
        }
    }
    public class HoldPositionConverter : IValueConverter
    {
        public object Convert(
        object value,
        Type targetType,
        object parameter,
        string language)
        {
            var args = (HoldingRoutedEventArgs)value;
            var element = (FrameworkElement)parameter;

            return args.GetPosition(element);
        }

        public object ConvertBack(
            object value,
            Type targetType,
            object parameter,
            string language)
        {
            throw new NotImplementedException();
        }
    }
    public class ManipStartedPositionConverter : IValueConverter
    {
        public object Convert(
        object value,
        Type targetType,
        object parameter,
        string language)
        {
            var args = (ManipulationStartedRoutedEventArgs)value;
            var element = (FrameworkElement)parameter;

            return args.Position;
        }

        public object ConvertBack(
            object value,
            Type targetType,
            object parameter,
            string language)
        {
            throw new NotImplementedException();
        }
    }
    public class ManipDeltaPositionConverter : IValueConverter
    {
        public object Convert(
        object value,
        Type targetType,
        object parameter,
        string language)
        {
            var args = (ManipulationDeltaRoutedEventArgs)value;
            var element = (FrameworkElement)parameter;

            return args.Position;
        }

        public object ConvertBack(
            object value,
            Type targetType,
            object parameter,
            string language)
        {
            throw new NotImplementedException();
        }
    }
    public class ManipCompletedPositionConverter : IValueConverter
    {
        public object Convert(
        object value,
        Type targetType,
        object parameter,
        string language)
        {
            var args = (ManipulationCompletedRoutedEventArgs)value;
            var element = (FrameworkElement)parameter;

            return args.Position;
        }

        public object ConvertBack(
            object value,
            Type targetType,
            object parameter,
            string language)
        {
            throw new NotImplementedException();
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
    abstract class ViewModelBase : INotifyPropertyChanged
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
