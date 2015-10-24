using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Runtime.Serialization;
using System.Text;
using System.Threading.Tasks;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Shapes;

namespace GrappBox.CustomControler
{
    class CustomCanvas : Canvas
    {
        #region Constructor
        public CustomCanvas() : base()
        { }
        #endregion Constructor
        public static readonly DependencyProperty ObjectList =
            DependencyProperty.Register("ShapeList", typeof(List<ShapeControler>), typeof(CustomCanvas), new PropertyMetadata(null, OnValueChanged));

        private static void OnValueChanged(DependencyObject d, DependencyPropertyChangedEventArgs e)
        {
            CustomCanvas source = d as CustomCanvas;
            source.Children.Clear();
            List<ShapeControler> val = e.NewValue as List<ShapeControler>;
            Debug.WriteLine("Notified");
            foreach (ShapeControler sc in val)
            {
                source.Children.Add(sc.BaseShape);
                Canvas.SetLeft(sc.BaseShape, sc.Pos.X);
                Canvas.SetTop(sc.BaseShape, sc.Pos.Y);
            }
        }

        public List<ShapeControler> ShapeList
        {
            get { return (List<ShapeControler>)GetValue(ObjectList); }
            set { SetValue(ObjectList, value); }
        }
    }
}
