using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;

// Pour plus d'informations sur le modèle d'élément Boîte de dialogue de contenu, voir la page http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.CustomControls
{
    public class BrushesPair
    {
        public string Label { get; set; }
        public double Size { get; set; }
        public BrushesPair()
        { }
    }
    public sealed partial class BrushDialog : ContentDialog
    {
        public static readonly ObservableCollection<BrushesPair> Brushes = new ObservableCollection<BrushesPair>()
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
        public BrushDialog(double brushSize = 1.0)
        {
            SelectedThickness = brushSize;
            this.InitializeComponent();
            BrushListView.ItemsSource = BrushDialog.Brushes;
            BrushListView.SelectedValuePath = "Size";
        }

        private void BrushListView_ItemClick(object sender, ItemClickEventArgs e)
        {
            BrushesPair item = e.ClickedItem as BrushesPair;
            SelectedThickness = item.Size;
            this.Hide();
        }
    }
}