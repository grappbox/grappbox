using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using System.Threading.Tasks;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;

namespace Grappbox.CustomControls
{
    public sealed partial class ColorDialog : ContentDialog
    {
        public static List<SolidColorBrush> colors = null;
        static ColorDialog()
        {
            if (colors == null)
            {
                colors = new List<SolidColorBrush>();
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
        }

        public DependencyProperty SelectedColorProperty = DependencyProperty
            .Register("SelectedColor", typeof(SolidColorBrush), typeof(ColorDialog), null);
        public SolidColorBrush SelectedColor
        {
            get { return (SolidColorBrush)GetValue(SelectedColorProperty); }
            set { SetValue(SelectedColorProperty, value); }
        }
        public ColorDialog(SolidColorBrush brush)
        {
            this.FullSizeDesired = true;
            SelectedColor = brush;
            this.InitializeComponent();
            GridViewColors.ItemsSource = colors;
        }

        private void GridViewColors_ItemClick(object sender, ItemClickEventArgs e)
        {
            SelectedColor = e.ClickedItem as SolidColorBrush;
            this.Hide();
        }
    }
}