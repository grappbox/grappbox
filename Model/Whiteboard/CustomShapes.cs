using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.Foundation;
using Windows.UI.Text;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Shapes;

namespace GrappBox.Model
{
    class CustomLine : ICustomShape
    {
        private Line elem;
        public CustomLine()
        {
            elem = new Line();
        }
        public UIElement GetElement()
        {
            return elem;
        }
        public void Initialize(string txt, bool bold, bool italic, SolidColorBrush stroke, int size)
        {
            return;
        }
        public void Initialize(Point pos, SolidColorBrush stroke, SolidColorBrush fill, double thickness)
        {
            elem.Stroke = stroke;
            elem.StrokeThickness = thickness;
            elem.X1 = pos.X;
            elem.Y1 = pos.Y;
            elem.X2 = pos.X;
            elem.Y2 = pos.Y;
        }

        public void Update(Point p)
        {
            elem.X2 = p.X;
            elem.Y2 = p.Y;
        }

        public void Initialize(PointCollection pos, SolidColorBrush border, SolidColorBrush fill, double thickness)
        {
            return;
        }
    }
    class CustomRectangle : ICustomShape
    {
        private Point origin;
        private Rectangle elem;
        public CustomRectangle()
        {
            origin = new Point();
            elem = new Rectangle();
        }
        public UIElement GetElement()
        {
            return elem;
        }
        public void Initialize(string txt, bool bold, bool italic, SolidColorBrush stroke, int size)
        {
            return;
        }
        public void Initialize(PointCollection pos, SolidColorBrush border, SolidColorBrush fill, double thickness)
        {
            return;
        }
        public void Initialize(Point pos, SolidColorBrush stroke, SolidColorBrush fill, double thickness)
        {
            elem.Width = 1;
            elem.Height = 1;
            elem.Stroke = stroke;
            elem.StrokeThickness = thickness;
            elem.Fill = fill;
            origin.X = pos.X;
            origin.Y = pos.Y;
        }

        public void Update(Point p)
        {
            elem.Width = ShapeControler.AbsoluteDiff(p.X, origin.X);
            elem.Height = ShapeControler.AbsoluteDiff(p.Y, origin.Y);
            if (p.X < origin.X)
                Canvas.SetLeft(elem, p.X);
            if (p.Y < origin.Y)
                Canvas.SetTop(elem, p.Y);
        }
    }
    class CustomEllipse : ICustomShape
    {
        private Point origin;
        private Ellipse elem;
        public CustomEllipse()
        {
            origin = new Point();
            elem = new Ellipse();
        }
        public UIElement GetElement()
        {
            return elem;
        }
        public void Initialize(PointCollection pos, SolidColorBrush border, SolidColorBrush fill, double thickness)
        {
            return;
        }
        public void Initialize(string txt, bool bold, bool italic, SolidColorBrush stroke, int size)
        {
            return;
        }
        public void Initialize(Point pos, SolidColorBrush stroke, SolidColorBrush fill, double thickness)
        {
            elem.Width = 1;
            elem.Height = 1;
            elem.Stroke = stroke;
            elem.StrokeThickness = thickness;
            elem.Fill = fill;
            origin.X = pos.X;
            origin.Y = pos.Y;
        }

        public void Update(Point p)
        {
            elem.Width = ShapeControler.AbsoluteDiff(p.X, origin.X);
            elem.Height = ShapeControler.AbsoluteDiff(p.Y, origin.Y);
            if (p.X < origin.X)
                Canvas.SetLeft(elem, p.X);
            if (p.Y < origin.Y)
                Canvas.SetTop(elem, p.Y);
        }
    }
    class Pen : ICustomShape
    {
        private Point origin;
        private Polyline elem;
        private PointCollection pc;
        public Pen() : base()
        {
            origin = new Point();
            elem = new Polyline();
            pc = new PointCollection();
        }
        public void Initialize(string txt, bool bold, bool italic, SolidColorBrush stroke, int size)
        {
            return;
        }
        public void Initialize(Point pos, SolidColorBrush border, SolidColorBrush fill, double thickness)
        {
            elem.Stroke = border;
            elem.StrokeThickness = thickness;
            origin.X = pos.X;
            origin.Y = pos.Y;
            pc.Add(origin);
            elem.Points = pc;
        }
        public void Initialize(PointCollection pos, SolidColorBrush border, SolidColorBrush fill, double thickness)
        {
            elem.Stroke = border;
            elem.StrokeThickness = thickness;
            origin.X = pos[0].X;
            origin.Y = pos[0].Y;
            foreach (Point p in pos)
               pc.Add(p);
            elem.Points = pc;
        }
        public void Update(Point p)
        {
            if (p == null)
                return;
            if (ShapeControler.AbsoluteDiff(p.X, origin.X) < 4.0 && ShapeControler.AbsoluteDiff(p.Y, origin.Y) < 4.0)
                return;
            pc.Add(p);
            origin.X = p.X;
            origin.Y = p.Y;
        }

        public UIElement GetElement()
        {
            return elem;
        }
    }
    class Lozenge : ICustomShape
    {
        private double width;
        private double height;
        private Point origin;
        private Polygon elem;
        private PointCollection pc;
        Point Left
        {
            get { return pc[0]; }
            set { pc[0] = value; }
        }
        Point Top
        {
            get { return pc[1]; }
            set { pc[1] = value; }
        }
        Point Right
        {
            get { return pc[2]; }
            set { pc[2] = value; }
        }
        Point Bottom
        {
            get { return pc[3]; }
            set { pc[3] = value; }
        }
        public Lozenge()
        {
            origin = new Point();
            pc = new PointCollection();
            elem = new Polygon();
        }
        public void Initialize(string txt, bool bold, bool italic, SolidColorBrush stroke, int size)
        {
            return;
        }
        public void Initialize(PointCollection pos, SolidColorBrush border, SolidColorBrush fill, double thickness)
        {
            return;
        }
        public void Initialize(Point pos, SolidColorBrush border, SolidColorBrush fill, double thickness)
        {
            height = 0;
            width = 0;
            elem.Stroke = border;
            elem.Fill = fill;
            elem.StrokeThickness = thickness;
            origin.X = pos.X;
            origin.Y = pos.Y;
            for (int i = 0; i < 4; ++i)
                pc.Add(new Point());
            elem.Points = pc;
        }
        public void Update(Point p)
        {
            System.GC.Collect(50, GCCollectionMode.Forced);
            width = ShapeControler.AbsoluteDiff(p.X, origin.X);
            height = ShapeControler.AbsoluteDiff(p.Y, origin.Y);
            Point left = new Point();
            Point top = new Point();
            Point right = new Point();
            Point bottom = new Point();
            left.X = 0;
            left.Y = height / 2;
            top.X = width / 2;
            top.Y = 0;
            right.X = width;
            right.Y = height / 2;
            bottom.X = width / 2;
            bottom.Y = height;
            Left = left;
            Top = top;
            Right = right;
            Bottom = bottom;
            if (p.X < origin.X)
                Canvas.SetLeft(elem, p.X);
            if (p.Y < origin.Y)
                Canvas.SetTop(elem, p.Y);
        }
        public UIElement GetElement()
        {
            return elem;
        }
    }
    class CustomText : ICustomShape
    {
        private TextBlock txtBlock;
        public UIElement GetElement()
        {
            return txtBlock;
        }
        public void Initialize(PointCollection pos, SolidColorBrush border, SolidColorBrush fill, double thickness)
        {
            return;
        }
        public void Initialize(string txt, bool bold, bool italic, SolidColorBrush stroke, int size)
        {
            txtBlock = new TextBlock();
            txtBlock.FontSize = size;
            txtBlock.Text = txt;
            txtBlock.Foreground = stroke;
            txtBlock.FontWeight = bold ? FontWeights.Bold : FontWeights.Normal;
            txtBlock.FontStyle = italic ? FontStyle.Italic : FontStyle.Normal;
        }
        public void Initialize(Point pos, SolidColorBrush stroke, SolidColorBrush fill, double thickness)
        {
            return;
        }

        public void Update(Point p)
        {
            Canvas.SetLeft(txtBlock, p.X);
            Canvas.SetTop(txtBlock, p.Y);
        }
    }
}