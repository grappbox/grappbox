using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.UI;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media;

namespace GrappBox.CustomControls
{
    /// <summary>
    /// Data to represent an item in the nav menu.
    /// </summary>
    public class NavMenuItem : INotifyPropertyChanged
    {
        private string _label;
        public string Label
        {
            get { return _label; }
            set { _label = value;
                this.OnPropertyChanged("PageHeaderTitle");
            }
        }
        public String Symbol { get; set; }
        public SolidColorBrush ForegroundColor { get; set; }

        private SolidColorBrush defaultColorBrush = new SolidColorBrush(Colors.Black);

        private SolidColorBrush _selectedColorBrush = new SolidColorBrush(Colors.Black);

        public SolidColorBrush SelectedColorBrush
        {
            get { return _selectedColorBrush; }
            set
            {
                _selectedColorBrush = value;
                this.OnPropertyChanged("SelectedColorBrush");
                this.OnPropertyChanged("PageHeaderColor");
            }
        }

        private bool _isSelected;

        public bool IsSelected
        {
            get { return _isSelected; }
            set
            {
                _isSelected = value;
                SelectedVis = value ? Visibility.Visible : Visibility.Collapsed;
                SelectedColorBrush = value ? ForegroundColor : defaultColorBrush;
                this.OnPropertyChanged("IsSelected");
            }
        }

        private Visibility _selectedVis = Visibility.Collapsed;

        public Visibility SelectedVis
        {
            get { return _selectedVis; }
            set
            {
                _selectedVis = value;
                this.OnPropertyChanged("SelectedVis");
            }
        }

        public Type DestPage { get; set; }
        public object Arguments { get; set; }

        public event PropertyChangedEventHandler PropertyChanged = delegate { };

        public void OnPropertyChanged(string propertyName)
        {
            // Raise the PropertyChanged event, passing the name of the property whose value has changed.
            this.PropertyChanged(this, new PropertyChangedEventArgs(propertyName));
        }
    }
}