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
using Windows.UI.Xaml.Shapes;

// The User Control item template is documented at http://go.microsoft.com/fwlink/?LinkId=234236

namespace GrappBox.CustomControler
{
    public enum ColorMod
    {
        BORDER, FILL
    }
    public sealed partial class Colorpan : UserControl
    {
        public static readonly DependencyProperty ModProperty =
            DependencyProperty.Register("Mod", typeof(ColorMod), typeof(Colorpan), null);

        public static readonly DependencyProperty PrimaryColorProperty =
            DependencyProperty.Register("SelectedColor", typeof(SolidColorBrush), typeof(Colorpan), null);

        public static readonly DependencyProperty SecondaryColorProperty =
            DependencyProperty.Register("SelectedFillColor", typeof(SolidColorBrush), typeof(Colorpan), null);

        private SolidColorBrush GreenSelect = new SolidColorBrush();
        private int selected;
        private int selectedFill;
        public ColorMod Mod
        {
            get { return (ColorMod)GetValue(ModProperty); }
            set { SetValue(ModProperty, value); }
        }
        private SolidColorBrush _selectedColor;
        public SolidColorBrush SelectedColor
        {
            get { return (SolidColorBrush)GetValue(PrimaryColorProperty); }
            set { SetValue(PrimaryColorProperty,value);}
        }
        private SolidColorBrush _selectedFillColor;
        public SolidColorBrush SelectedFillColor
        {
            get { return (SolidColorBrush)GetValue(SecondaryColorProperty); }
            set { SetValue(SecondaryColorProperty, value); }
        }
        public Colorpan()
        {
            GreenSelect.Color = Colors.Green;
            selected = 24;
            selectedFill = 25;
            this.InitializeComponent();
            foreach (Ellipse elem in ColorpanGrid.Children)
            {
                elem.Tapped += Elem_Tapped;
            }
        }
        private void Elem_Tapped(object sender, TappedRoutedEventArgs e)
        {
            Ellipse elem = sender as Ellipse;
            if (Mod == ColorMod.BORDER)
            {
                SelectedColor = (SolidColorBrush)elem.Fill;
                selected = int.Parse(elem.Name.Substring(1));
            }
            else
            {
                SelectedFillColor = (SolidColorBrush)elem.Fill;
                selectedFill = int.Parse(elem.Name.Substring(1));
            }
            this.Visibility = Visibility.Collapsed;
        }
    }
}
