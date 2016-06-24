using GrappBox.ViewModel;
using GrappBox.Model;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Runtime.Serialization;
using System.Text;
using System.Threading.Tasks;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Shapes;

namespace GrappBox.CustomControler
{
    class CustomCanvas : Canvas
    {
        private readonly ManipulationModes DefaultManipModes = ManipulationModes.TranslateX | ManipulationModes.TranslateY | ManipulationModes.System;
        public CustomCanvas() : base()
        {
            this.Width = 4096;
            this.Height = 2160;
            this.VerticalAlignment = VerticalAlignment.Top;
            this.HorizontalAlignment = HorizontalAlignment.Left;
            this.ManipulationMode = DefaultManipModes;
        }
        public void ChangeManipMode()
        {
            if (ManipulationMode == ManipulationModes.None)
                this.ManipulationMode = DefaultManipModes;
            else
                this.ManipulationMode = ManipulationModes.None;
        }
        public static readonly DependencyProperty CurrentDrawDependency =
            DependencyProperty.Register("CurrDraw", typeof(ShapeControler), typeof(CustomCanvas), new PropertyMetadata(null, OnValueChanged));

        private static void OnValueChanged(DependencyObject d, DependencyPropertyChangedEventArgs e)
        {
            CustomCanvas source = d as CustomCanvas;
            ShapeControler val = e.NewValue as ShapeControler;
            if (val == null)
                return;
            Debug.WriteLine("Canvas on value changed");
            source.Children.Add(val.UiElem);
            if (val.Type == WhiteboardTool.HANDWRITING || val.Type == WhiteboardTool.LINE)
            {
                Canvas.SetLeft(val.UiElem, 0);
                Canvas.SetTop(val.UiElem, 0);
            }
            else
            {
                Canvas.SetLeft(val.UiElem, val.PosOrigin.X);
                Canvas.SetTop(val.UiElem, val.PosOrigin.Y);
            }
        }

        public ShapeControler CurrDraw
        {
            get { return (ShapeControler)GetValue(CurrentDrawDependency); }
            set { SetValue(CurrentDrawDependency, value); }
        }
    }
}
