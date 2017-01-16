using Grappbox.ViewModel;
using Grappbox.Model;
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
using System.Collections.ObjectModel;
using Grappbox.Model.Whiteboard;
using Windows.UI.Popups;

namespace Grappbox.CustomControls
{
    class CustomCanvas : Canvas
    {
        private int index = 0;
        private readonly ManipulationModes DefaultManipModes = ManipulationModes.TranslateX | ManipulationModes.TranslateY | ManipulationModes.System;
        public CustomCanvas() : base()
        {
            this.Width = 4096;
            this.Height = 2160;
            this.VerticalAlignment = VerticalAlignment.Top;
            this.HorizontalAlignment = HorizontalAlignment.Left;
            this.ManipulationMode = DefaultManipModes;
            this.ObjectList = new ObservableCollection<ShapeControler>();
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
            val.Index = source.index;
            source.ObjectList.Add(val);
            source.Children.Add(val.UiElem);
            ++source.index;
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

        public void AddNewElement(WhiteboardObject wo)
        {
            ShapeControler sc = ShapeModelConverter.ModelToShape(wo.Object, wo.Id);
            sc.Index = index;
            ObjectList.Add(sc);
            Children.Add(sc.UiElem);
            if (sc.Type == WhiteboardTool.HANDWRITING || sc.Type == WhiteboardTool.LINE)
            {
                Canvas.SetLeft(sc.UiElem, 0);
                Canvas.SetTop(sc.UiElem, 0);
            }
            else
            {
                Canvas.SetLeft(sc.UiElem, sc.PosOrigin.X);
                Canvas.SetTop(sc.UiElem, sc.PosOrigin.Y);
            }
            ++index;
        }

        public bool DeleteElement(int id)
        {
            ShapeControler toDel = this.ObjectList.FirstOrDefault(item => item.Id == id);
            if (toDel == null)
            {
                return false;
            }
            this.Children.RemoveAt(toDel.Index);
            this.ObjectList.Remove(toDel);
            return true;
        }

        public void Clear()
        {
            Children.Clear();
        }

        public ObservableCollection<ShapeControler> ObjectList { get; set; }

        public ShapeControler CurrDraw
        {
            get { return (ShapeControler)GetValue(CurrentDrawDependency); }
            set { SetValue(CurrentDrawDependency, value); }
        }
    }
}
