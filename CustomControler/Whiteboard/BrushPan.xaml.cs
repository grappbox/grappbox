using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
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

// The User Control item template is documented at http://go.microsoft.com/fwlink/?LinkId=234236

namespace GrappBox.CustomControler
{
    public sealed partial class BrushPan : UserControl
    {
        public class BrushesPair
        {
            public string Label { get; set; }
            public double Size { get; set; }
            public BrushesPair()
            {}
        }
        public static readonly List<BrushesPair> Brushes = new List<BrushesPair>()
        {
            new BrushesPair(){ Label="0.5", Size= 0.5},
            new BrushesPair(){ Label="1.0", Size= 1.0},
            new BrushesPair(){ Label="1.5", Size= 1.5},
            new BrushesPair(){ Label="2.0", Size= 2.0},
            new BrushesPair(){ Label="2.5", Size= 2.5},
            new BrushesPair(){ Label="3.0", Size= 3.0},
            new BrushesPair(){ Label="4.0", Size= 4.0},
            new BrushesPair(){ Label="5.0", Size= 5.0}
        };
        public double SelectedThickness { get; set; }
        public BrushPan()
        {
            SelectedThickness = 0.0;
            this.InitializeComponent();
            BrushListView.ItemsSource = Brushes;
            BrushListView.SelectedValuePath = "Size";
        }
        public async System.Threading.Tasks.Task WaitForSelect()
        {
            await Task.Run(() =>
            {
                while (SelectedThickness == 0.0) ;
            });
        }

        private void BrushListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            SelectedThickness = (double)BrushListView.SelectedValue;
        }
    }
}