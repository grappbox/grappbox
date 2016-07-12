using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using System.Threading;
using System.Threading.Tasks;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Windows.UI.Xaml.Shapes;

// The User Control item template is documented at http://go.microsoft.com/fwlink/?LinkId=234236

namespace GrappBox.CustomControler
{
    public sealed partial class Colorpan : UserControl
    {
        public static ObservableCollection<SolidColorBrush> colors = null;
        private SolidColorBrush _selectedColor;
        public SolidColorBrush SelectedColor
        {
            get { return _selectedColor; }
            set { _selectedColor = value; }
        }
        public Colorpan()
        {
            SelectedColor = null;
            if (colors == null)
            {
                colors = new ObservableCollection<SolidColorBrush>();
                for (int i = 1; i < 26; ++i)
                {
                    object o;
                    if (Application.Current.Resources.TryGetValue("Color_" + i + "Brush", out o) == true)
                    {
                        SolidColorBrush scb = o as SolidColorBrush;
                        colors.Add(scb);
                    }
                }
            }
            this.InitializeComponent();
            GridViewColors.ItemsSource = colors;
        }
        public async System.Threading.Tasks.Task WaitForSelect(CancellationToken tok)
        {
            await Task.Run(() =>
            {
                while (SelectedColor == null) {}
            }, tok);
        }

        private void GridViewColors_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            GridView gv = sender as GridView;
            SelectedColor = gv.SelectedValue as SolidColorBrush;
        }
    }
}
