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

namespace GrappBox.ViewModel
{
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
    public class ManipPositionConverter : IValueConverter
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
    abstract class  ViewModelBase : INotifyPropertyChanged
    {
        public event PropertyChangedEventHandler PropertyChanged;
        public void NotifyPropertyChanged(string property)
        {

            if (PropertyChanged != null)
            {
                PropertyChanged(this, new PropertyChangedEventArgs(property));
                Debug.WriteLine("Notify " + property);
            }
        }
    }
}
