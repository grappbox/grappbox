using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
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

// The User Control item template is documented at http://go.microsoft.com/fwlink/?LinkId=234236

namespace GrappBox.CustomControler
{
    public sealed partial class BrushPan : UserControl
    {
        public static readonly DependencyProperty ThicknessProperty =
            DependencyProperty.Register("SelectedThickness", typeof(double), typeof(BrushPan), null);
        private double[] brushes = { 0.5, 1.0, 1.5, 2.0, 2.5, 3.0, 4.0, 5.0};
        private int selected = 1;
        public double SelectedThickness
        {
            get { return (double)GetValue(ThicknessProperty); }
            set { SetValue(ThicknessProperty,value); }
        }
        public BrushPan()
        {
            SelectedThickness = 1.0;
            this.InitializeComponent();
        }
        private void Elem_Tapped(object sender, TappedRoutedEventArgs e)
        {
            var res = BrushGrid.Children.Where(SortBorder);
            Border elem = res.First<UIElement>() as Border;
            elem.BorderBrush = new SolidColorBrush(Colors.Transparent);
            Grid grid = sender as Grid;
            selected = int.Parse(grid.Name.Substring(1));
            res = BrushGrid.Children.Where(SortBorder);
            elem = res.First<UIElement>() as Border;
            SelectedThickness = brushes[selected];
            elem.BorderBrush = new SolidColorBrush(Colors.Black);

            this.Visibility = Visibility.Collapsed;
        }
        private bool SortBorder(UIElement elem)
        {
            var tmp = elem as FrameworkElement;
            if (tmp.Name.Contains("b" + selected.ToString()))
                return true;
            return false;            
        }
    }
}