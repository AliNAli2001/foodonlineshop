import * as React from 'react';

type DivProps = React.HTMLAttributes<HTMLDivElement>;
type HeadingProps = React.HTMLAttributes<HTMLHeadingElement>;
type ParagraphProps = React.HTMLAttributes<HTMLParagraphElement>;

function cx(...classes: Array<string | undefined | false>) {
  return classes.filter(Boolean).join(' ');
}

const Card = React.forwardRef<HTMLDivElement, DivProps>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cx('rounded-2xl border border-white/10 bg-white/[0.04] text-slate-100 shadow-sm', className)}
    {...props}
  />
));
Card.displayName = 'Card';

const CardHeader = React.forwardRef<HTMLDivElement, DivProps>(({ className, ...props }, ref) => (
  <div ref={ref} className={cx('flex flex-col space-y-1.5 p-5', className)} {...props} />
));
CardHeader.displayName = 'CardHeader';

const CardTitle = React.forwardRef<HTMLHeadingElement, HeadingProps>(({ className, ...props }, ref) => (
  <h3 ref={ref} className={cx('text-lg font-semibold leading-none tracking-tight text-white', className)} {...props} />
));
CardTitle.displayName = 'CardTitle';

const CardDescription = React.forwardRef<HTMLParagraphElement, ParagraphProps>(({ className, ...props }, ref) => (
  <p ref={ref} className={cx('text-sm text-slate-300', className)} {...props} />
));
CardDescription.displayName = 'CardDescription';

const CardContent = React.forwardRef<HTMLDivElement, DivProps>(({ className, ...props }, ref) => (
  <div ref={ref} className={cx('px-5 pb-5', className)} {...props} />
));
CardContent.displayName = 'CardContent';

const CardFooter = React.forwardRef<HTMLDivElement, DivProps>(({ className, ...props }, ref) => (
  <div ref={ref} className={cx('flex items-center p-5 pt-0', className)} {...props} />
));
CardFooter.displayName = 'CardFooter';

export { Card, CardHeader, CardFooter, CardTitle, CardDescription, CardContent };
